<?php

namespace App\Http\Controllers;

use App\Models\SupplierMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class supplierController extends Controller
{
    public function index()
    {
        $userId = request()->cookies->get('GTA');
        $suppliers = SupplierMaster::where('created_by', $userId)
            ->where('status', '1')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('supplier_code', 'like', '%'.$search.'%')
                        ->orWhere('supplier_name', 'like', '%'.$search.'%')
                        ->orWhere('contact_person', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('supplier_id')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function createSupplier()
    {
        $userId = request()->cookies->get('GTA');

        // Find last supplier of this user
        $lastSupplier = SupplierMaster::withTrashed()
            ->where('created_by', $userId)
            ->orderByDesc('supplier_id')
            ->first();

        $nextNumber = 1;

        if ($lastSupplier) {
            $lastNumber = (int) substr($lastSupplier->supplier_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        $supplierCode =
            'SUP'.
            str_pad($userId, 2, '0', STR_PAD_LEFT).
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('suppliers.create', compact('supplierCode'));
    }

    public function createSupplierPost(Request $request)
    {
        $validatedData = $request->validate([
            'lf_supplier_code' => 'required|unique:suppliermaster,supplier_code|max:20',
            'tb_supplierName' => 'required|max:100',
            'tb_email' => 'nullable|email|max:100|unique:suppliermaster,email',
            'tb_phone' => 'required|max:20',
            'tb_contactPerson' => 'nullable|max:100',
            'tb_gstNumber' => 'nullable|max:15|unique:suppliermaster,gst_number',
            'tb_supplierAddress' => 'nullable',
        ], [
            'tb_supplierName.required' => 'Supplier name is required.',
            'tb_phone.required' => 'Phone number is required.',
            'tb_gstNumber.unique' => 'GST number is already in use.',
            'tb_email.unique' => 'Email is already in use.',
            'tb_contactPerson.max' => 'Contact person name is too long.',
        ], [
            'lf_supplier_code' => 'Supplier Code',
            'tb_supplierName' => 'Supplier Name',
            'tb_email' => 'Email',
            'tb_phone' => 'Phone',
            'tb_gstNumber' => 'GST Number',
            'tb_supplierAddress' => 'Supplier Address',
            'tb_contactPerson' => 'Contact Person',
        ]);

        try {
            $userId = request()->cookies->get('GTA');
            DB::transaction(function () use ($validatedData, $userId) {
                SupplierMaster::create([
                    'supplier_code' => $validatedData['lf_supplier_code'],
                    'supplier_name' => $validatedData['tb_supplierName'],
                    'email' => $validatedData['tb_email'] ?? null,
                    'phone' => $validatedData['tb_phone'],
                    'gst_number' => $validatedData['tb_gstNumber'] ?? null,
                    'supplier_address' => $validatedData['tb_supplierAddress'] ?? null,
                    'contact_person' => $validatedData['tb_contactPerson'] ?? null,
                    'created_by' => $userId,
                ]);
            });

            return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating supplier: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    public function viewSupplier($id)
    {
        $supplier = SupplierMaster::findOrFail($id);
        return view('suppliers.viewSupplier', compact('supplier'));
    }

    public function editSupplier($id)
    {
        $supplier = SupplierMaster::findOrFail($id);
        return view('suppliers.editSupplier', compact('supplier'));
    }

    public function editSupplierPost(Request $request, $id)
    {
        $supplier = SupplierMaster::findOrFail($id);
        $validatedData = $request->validate([
            'tb_supplierName' => 'required|max:100',
            'tb_email' => 'nullable|email|max:100|unique:suppliermaster,email,'.$supplier->supplier_id.',supplier_id',
            'tb_phone' => 'required|max:20',
            'tb_contactPerson' => 'nullable|max:100',
            'tb_gstNumber' => 'nullable|max:15|unique:suppliermaster,gst_number,'.$supplier->supplier_id.',supplier_id',
            'tb_supplierAddress' => 'nullable',
        ], [
            'tb_supplierName.required' => 'Supplier name is required.',
            'tb_phone.required' => 'Phone number is required.',
            'tb_gstNumber.unique' => 'GST number is already in use.',
            'tb_email.unique' => 'Email is already in use.',
            'tb_contactPerson.max' => 'Contact person name is too long.',
        ], [
            'tb_supplierName' => 'Supplier Name',
            'tb_email' => 'Email',
            'tb_phone' => 'Phone',
            'tb_gstNumber' => 'GST Number',
            'tb_supplierAddress' => 'Supplier Address',
            'tb_contactPerson' => 'Contact Person',
        ]);

        try {
            $userId = request()->cookies->get('GTA');
            DB::transaction(function () use ($supplier, $validatedData, $userId) {
                $supplier->update([
                    'supplier_name' => $validatedData['tb_supplierName'],
                    'email' => $validatedData['tb_email'] ?? null,
                    'phone' => $validatedData['tb_phone'],
                    'gst_number' => $validatedData['tb_gstNumber'] ?? null,
                    'supplier_address' => $validatedData['tb_supplierAddress'] ?? null,
                    'contact_person' => $validatedData['tb_contactPerson'] ?? null,
                    'updated_by' => $userId,
                ]);
            });

            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating supplier: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while updating the supplier. Please try again.')->withInput();
        }
    }

    public function deleteSupplier($id)
    {
        try {
            DB::transaction(function () use ($id) {
                SupplierMaster::where('supplier_id', $id)
                    ->update([
                    'status' => '0',
                    'updated_by' => request()->cookie('GTA'),
                    'deleted_at' => now(),
                ]);
            });

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting supplier: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the supplier. Please try again.');
        }
    }
}