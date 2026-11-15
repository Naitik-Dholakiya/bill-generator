<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class customerController extends Controller
{
    //
    public function index()
    {
        $userId = request()->cookies->get('GTA');
        $customers = Customer::where('created_by', $userId)
            ->where('status', '1')
            ->orderByDesc('customer_id')
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function createCustomer()
    {
        $userId = request()->cookies->get('GTA');

        // Find last customer of this user
        $lastCustomer = Customer::where('created_by', $userId)
            ->latest('customer_id')
            ->first();

        $nextNumber = 1;

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        $customerCode =
            'CUS'.
            str_pad($userId, 2, '0', STR_PAD_LEFT).
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('customers.create', compact('customerCode'));
    }

    public function createCustomerPost(Request $request)
    {
        $userId = (int) $request->attributes->get('authenticated_user_id');
        $validatedData = $request->validate([
            'customer_code' => ['required', 'string', 'max:20', Rule::unique('customermaster', 'customer_code')],
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('customermaster', 'email')],
            'phone' => ['required', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:15', 'regex:/^[0-9A-Z]{15}$/i', Rule::unique('customermaster', 'gst_number')],
            'billing_address' => ['nullable', 'string', 'max:2000'],
            'shipping_address' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($validatedData, $userId): void {
                Customer::create([
                'customer_code' => $validatedData['customer_code'],
                'customer_name' => $validatedData['customer_name'],
                'email' => $validatedData['email'] ?? null,
                'phone' => $validatedData['phone'],
                'gst_number' => $validatedData['gst_number'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'created_by' => $userId,
                'status' => '1',
                ]);
            });

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Unable to save the customer. Please try again.')
                ->withInput();
        }
    }

    public function viewCustomer($id)
    {
        $customer = Customer::findOrFail($id);

        return view('customers.view', compact('customer'));
    }

    public function editCustomer($id)
    {
        $customer = Customer::findOrFail($id);

        return view('customers.edit', compact('customer'));
    }

    public function editCustomerPost($id, Request $request)
    {
        $customer = Customer::findOrFail($id);
        $validatedData = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('customermaster', 'email')->ignore($customer->customer_id, 'customer_id')],
            'phone' => ['required', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:15', 'regex:/^[0-9A-Z]{15}$/i', Rule::unique('customermaster', 'gst_number')->ignore($customer->customer_id, 'customer_id')],
            'billing_address' => ['nullable', 'string', 'max:2000'],
            'shipping_address' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $userId = (int) $request->attributes->get('authenticated_user_id');
            DB::transaction(function () use ($customer, $validatedData, $userId): void {
                $customer->update([
                'customer_name' => $validatedData['customer_name'],
                'email' => $validatedData['email'] ?? null,
                'phone' => $validatedData['phone'],
                'gst_number' => $validatedData['gst_number'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'updated_by' => $userId,
                ]);
            });

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Unable to update the customer. Please try again.')
                ->withInput();
        }
    }

    public function deleteCustomer($id)
    {
        try {
            DB::beginTransaction();

            Customer::where('customer_id', $id)
                ->update([
                    'status' => '0',
                    'updated_by' => request()->cookie('GTA'),
                    'deleted_at' => now(),
                ]);
            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
