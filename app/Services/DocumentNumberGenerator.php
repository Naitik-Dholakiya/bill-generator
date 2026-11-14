<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    public function previewInvoiceNumber(int $userId): string
    {
        return $this->preview('invoice', $userId);
    }

    public function nextInvoiceNumber(int $userId): string
    {
        return $this->next('invoice', $userId);
    }

    public function previewSupplierBillNumber(int $userId): string
    {
        return $this->preview('supplier_bill', $userId);
    }

    public function nextSupplierBillNumber(int $userId): string
    {
        return $this->next('supplier_bill', $userId);
    }

    private function preview(string $type, int $userId): string
    {
        [$table, $column, $primaryKey, $prefix] = $this->documentDetails($type, $userId);
        $row = DB::table('document_sequences')
            ->where('document_type', $type)
            ->where('user_id', $userId)
            ->where('year', now()->year)
            ->first();
        $sequence = max((int) ($row->next_sequence ?? 1), $this->existingNextSequence($table, $column, $primaryKey, $userId, $prefix));

        return $this->format($prefix, $sequence);
    }

    private function next(string $type, int $userId): string
    {
        [$table, $column, $primaryKey, $prefix] = $this->documentDetails($type, $userId);
        $year = now()->year;
        $initialNextSequence = $this->existingNextSequence($table, $column, $primaryKey, $userId, $prefix);
        $now = now();

        DB::table('document_sequences')->insertOrIgnore([
            'document_type' => $type,
            'user_id' => $userId,
            'year' => $year,
            'next_sequence' => $initialNextSequence,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequenceRow = DB::table('document_sequences')
            ->where('document_type', $type)
            ->where('user_id', $userId)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $sequence = max((int) $sequenceRow->next_sequence, $initialNextSequence);
        DB::table('document_sequences')
            ->where('document_sequence_id', $sequenceRow->document_sequence_id)
            ->update([
                'next_sequence' => $sequence + 1,
                'updated_at' => $now,
            ]);

        return $this->format($prefix, $sequence);
    }

    private function existingNextSequence(string $table, string $column, string $primaryKey, int $userId, string $prefix): int
    {
        $lastNumber = DB::table($table)
            ->where('created_by', $userId)
            ->where($column, 'like', $prefix.'%')
            ->orderByDesc($primaryKey)
            ->value($column);

        if (! is_string($lastNumber) || ! str_starts_with($lastNumber, $prefix)) {
            return 1;
        }

        $suffix = substr($lastNumber, strlen($prefix));

        return ctype_digit($suffix) ? ((int) $suffix + 1) : 1;
    }

    private function documentDetails(string $type, int $userId): array
    {
        $prefix = match ($type) {
            'invoice' => 'INV'.now()->year.$userId,
            'supplier_bill' => 'BILL'.now()->year.$userId,
            default => throw new \InvalidArgumentException('Unsupported document type.'),
        };
        $table = $type === 'invoice' ? 'invoicemaster' : 'purchasemaster';
        $column = $type === 'invoice' ? 'invoice_number' : 'purchase_number';
        $primaryKey = $type === 'invoice' ? 'invoice_id' : 'purchase_id';

        return [$table, $column, $primaryKey, $prefix];
    }

    private function format(string $prefix, int $sequence): string
    {
        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
