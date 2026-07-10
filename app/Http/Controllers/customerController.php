<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        try {

            $validatedData = $request->validate([
                'customer_code' => 'required|unique:customermaster,customer_code|max:20',
                'customer_name' => 'required|max:100',
                'email' => 'nullable|email|max:100|unique:customermaster,email',
                'phone' => 'required|max:20',
                'gst_number' => 'nullable|max:15|unique:customermaster,gst_number',
                'billing_address' => 'nullable',
                'shipping_address' => 'nullable',
            ], [
                'customer_name.required' => 'Customer name is required.',
                'customer_name.max' => 'Customer name must not exceed 100 characters.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'Email already exists.',
                'phone.required' => 'Phone number is required.',
                'gst_number.unique' => 'GST Number already exists.',
            ], [
                'customer_code' => 'Customer Code',
                'customer_name' => 'Customer Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'gst_number' => 'GST Number',
                'billing_address' => 'Billing Address',
                'shipping_address' => 'Shipping Address',
            ]);

            DB::beginTransaction();

            $userId = $request->cookie('GTA');

            Customer::create([
                'customer_code' => $validatedData['customer_code'],
                'customer_name' => $validatedData['customer_name'],
                'email' => $validatedData['email'] ?? null,
                'phone' => $validatedData['phone'],
                'gst_number' => $validatedData['gst_number'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'created_by' => $userId,
                'created_on' => now(),
                'active_status' => 1,
            ]);

            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer created successfully.');

        } catch (ValidationException $e) {

            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage())
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
        try {

            $customer = Customer::findOrFail($id);

            $validatedData = $request->validate([
                'customer_name' => 'required|max:100',
                'email' => 'nullable|email|max:100|unique:customermaster,email,'.$customer->customer_id.',customer_id',
                'phone' => 'required|max:20',
                'gst_number' => 'nullable|max:15|unique:customermaster,gst_number,'.$customer->customer_id.',customer_id',
                'billing_address' => 'nullable',
                'shipping_address' => 'nullable',
            ], [
                'customer_name.required' => 'Customer name is required.',
                'customer_name.max' => 'Customer name must not exceed 100 characters.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'Email already exists.',
                'phone.required' => 'Phone number is required.',
                'gst_number.unique' => 'GST Number already exists.',
            ], [
                'customer_name' => 'Customer Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'gst_number' => 'GST Number',
                'billing_address' => 'Billing Address',
                'shipping_address' => 'Shipping Address',
            ]);

            DB::beginTransaction();

            $userId = $request->cookie('GTA');

            $customer->update([
                'customer_name' => $validatedData['customer_name'],
                'email' => $validatedData['email'] ?? null,
                'phone' => $validatedData['phone'],
                'gst_number' => $validatedData['gst_number'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'updated_by' => $userId,
                'updated_on' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer updated successfully.');

        } catch (ValidationException $e) {

            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back
                ->with('error', $e->getMessage());
        }
    }

    public function deleteCustomer($id)
    {
        try {
            DB::beginTransaction();

            Customer::where('customer_id', $id)
                ->update([
                    'status' => 'inactive',
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
