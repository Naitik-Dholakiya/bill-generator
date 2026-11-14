<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function storeInvoice(Request $request, int $invoiceId)
    {
        return $this->store($request, 'invoice', $invoiceId);
    }

    public function voidInvoice(Request $request, int $invoiceId, int $paymentId)
    {
        return $this->void($request, 'invoice', $invoiceId, $paymentId);
    }

    public function storeSupplierBill(Request $request, int $billId)
    {
        return $this->store($request, 'supplier_bill', $billId);
    }

    public function voidSupplierBill(Request $request, int $billId, int $paymentId)
    {
        return $this->void($request, 'supplier_bill', $billId, $paymentId);
    }

    private function store(Request $request, string $type, int $documentId)
    {
        $tables = $this->tables($type);
        $userId = (int) $request->attributes->get('authenticated_user_id');

        DB::transaction(function () use ($request, $tables, $documentId, $userId): void {
            $document = DB::table($tables['document'])
                ->where($tables['document_id'], $documentId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            abort_if(! $document, 404, 'Document not found.');
            abort_if((float) $document->grand_total <= 0, 422, 'Payments are not available for a zero-value document.');

            $openingAmountIsUnknown = $document->opening_paid_amount === null;
            $validated = $request->validate([
                'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
                'paid_at' => ['required', 'date'],
                'method' => ['required', 'string', Rule::in(['cash', 'bank_transfer', 'card', 'upi', 'cheque', 'other'])],
                'reference_number' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'opening_paid_amount' => [
                    Rule::requiredIf($openingAmountIsUnknown),
                    'nullable',
                    'numeric',
                    'decimal:0,2',
                    'min:0',
                    'max:9999999999.99',
                ],
            ]);

            $openingAmount = $openingAmountIsUnknown
                ? $this->cents($validated['opening_paid_amount'])
                : $this->cents($document->opening_paid_amount);
            $recordedAmount = $this->cents(DB::table($tables['payments'])
                ->where($tables['payment_document_id'], $documentId)
                ->whereNull('voided_at')
                ->sum('amount'));
            $paymentAmount = $this->cents($validated['amount']);
            $grandTotal = $this->cents($document->grand_total);

            if ($openingAmountIsUnknown && $openingAmount > $grandTotal) {
                throw ValidationException::withMessages([
                    'opening_paid_amount' => 'The previous amount paid cannot exceed the document total.',
                ]);
            }

            if ($openingAmount + $recordedAmount + $paymentAmount > $grandTotal) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment cannot exceed the remaining document balance.',
                ]);
            }

            $now = now();
            if ($openingAmountIsUnknown) {
                DB::table($tables['document'])
                    ->where($tables['document_id'], $documentId)
                    ->update([
                        'opening_paid_amount' => $this->decimalAmount($openingAmount),
                        'updated_at' => $now,
                    ]);
            }

            DB::table($tables['payments'])->insert([
                $tables['payment_document_id'] => $documentId,
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'],
                'method' => $validated['method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $paidAmount = $openingAmount + $recordedAmount + $paymentAmount;
            DB::table($tables['document'])
                ->where($tables['document_id'], $documentId)
                ->update([
                    'payment_status' => $this->paymentStatus($paidAmount, $grandTotal),
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    private function void(Request $request, string $type, int $documentId, int $paymentId)
    {
        $tables = $this->tables($type);
        $userId = (int) $request->attributes->get('authenticated_user_id');
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($tables, $documentId, $paymentId, $userId, $validated): void {
            $document = DB::table($tables['document'])
                ->where($tables['document_id'], $documentId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            abort_if(! $document, 404, 'Document not found.');

            $payment = DB::table($tables['payments'])
                ->where($tables['payment_id'], $paymentId)
                ->where($tables['payment_document_id'], $documentId)
                ->whereNull('voided_at')
                ->lockForUpdate()
                ->first();
            abort_if(! $payment, 404, 'Payment not found.');

            $now = now();
            DB::table($tables['payments'])
                ->where($tables['payment_id'], $paymentId)
                ->update([
                    'voided_at' => $now,
                    'voided_by' => $userId,
                    'void_reason' => $validated['void_reason'],
                    'updated_at' => $now,
                ]);

            $openingAmount = $document->opening_paid_amount === null
                ? 0
                : $this->cents($document->opening_paid_amount);
            $recordedAmount = $this->cents(DB::table($tables['payments'])
                ->where($tables['payment_document_id'], $documentId)
                ->whereNull('voided_at')
                ->sum('amount'));

            DB::table($tables['document'])
                ->where($tables['document_id'], $documentId)
                ->update([
                    'payment_status' => $document->opening_paid_amount === null
                        ? $document->payment_status
                        : $this->paymentStatus($openingAmount + $recordedAmount, $this->cents($document->grand_total)),
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);
        });

        return back()->with('success', 'Payment voided; the reason has been recorded.');
    }

    private function tables(string $type): array
    {
        return match ($type) {
            'invoice' => [
                'document' => 'invoicemaster',
                'document_id' => 'invoice_id',
                'payments' => 'invoice_payments',
                'payment_id' => 'invoice_payment_id',
                'payment_document_id' => 'invoice_id',
            ],
            'supplier_bill' => [
                'document' => 'purchasemaster',
                'document_id' => 'purchase_id',
                'payments' => 'supplier_bill_payments',
                'payment_id' => 'supplier_bill_payment_id',
                'payment_document_id' => 'purchase_id',
            ],
            default => throw new \InvalidArgumentException('Unsupported payment document type.'),
        };
    }

    private function paymentStatus(int $paidAmount, int $grandTotal): string
    {
        if ($paidAmount >= $grandTotal) {
            return 'paid';
        }

        return $paidAmount > 0 ? 'partial' : 'pending';
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function decimalAmount(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
