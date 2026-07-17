<?php

namespace App\Http\Controllers;
use App\Models\SupplierMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class supplierController extends Controller
{
    public function index()
    {
        $userId = request()->cookies->get('GTA');
        $suppliers = SupplierMaster::where('created_by', $userId)
            ->where('status', '1')
            ->orderByDesc('supplier_id')
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function createSupplier()
    {
        $userId = request()->cookies->get('GTA');

        // Find last supplier of this user
        $lastSupplier = SupplierMaster::where('created_by', $userId)
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
        try {
            $validator = Validator::make($request->all(), [
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
                'tb_phone.unique' => 'Phone number is already in use.',
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

            $userId = request()->cookies->get('GTA');
            $validatedData['created_by'] = $userId;

            DB::beginTransaction();
            SupplierMaster::create([
                'supplier_code' => $validatedData['lf_supplier_code'],
                'supplier_name' => $validatedData['tb_supplierName'],
                'email' => $validatedData['tb_email'],
                'phone' => $validatedData['tb_phone'],
                'gst_number' => $validatedData['tb_gstNumber'],
                'supplier_address' => $validatedData['tb_supplierAddress'],
                'contact_person' => $validatedData['tb_contactPerson'],
                'created_by' => $userId,
            ]);

            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
        } catch (ValidationException $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while creating the supplier. Please try again.')->withInput();
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
        try {
            $supplier = SupplierMaster::findOrFail($id);

            $validatedData = $request->validate([
                'tb_supplierName' => 'required|max:100',
                'tb_email' => 'nullable|email|max:100|unique:suppliermaster,email,' . $supplier->supplier_id . ',supplier_id',
                'tb_phone' => 'required|max:20|unique:suppliermaster,phone,' . $supplier->supplier_id . ',supplier_id',
                'tb_contactPerson' => 'nullable|max:100',
                'tb_gstNumber' => 'nullable|max:15|unique:suppliermaster,gst_number,' . $supplier->supplier_id . ',supplier_id',
                'tb_supplierAddress' => 'nullable',
            ], [
                'tb_supplierName.required' => 'Supplier name is required.',
                'tb_phone.required' => 'Phone number is required.',
                'tb_gstNumber.unique' => 'GST number is already in use.',
                'tb_email.unique' => 'Email is already in use.',
                'tb_phone.unique' => 'Phone number is already in use.',
                'tb_contactPerson.max' => 'Contact person name is too long.',
            ], [
                'tb_supplierName' => 'Supplier Name',
                'tb_email' => 'Email',
                'tb_phone' => 'Phone',
                'tb_gstNumber' => 'GST Number',
                'tb_supplierAddress' => 'Supplier Address',
                'tb_contactPerson' => 'Contact Person',
            ]);

            $userId = request()->cookies->get('GTA');
            $validatedData['updated_by'] = $userId;

            DB::beginTransaction();
            $supplier->update([
                'supplier_name' => $validatedData['tb_supplierName'],
                'email' => $validatedData['tb_email'],
                'phone' => $validatedData['tb_phone'],
                'gst_number' => $validatedData['tb_gstNumber'],
                'supplier_address' => $validatedData['tb_supplierAddress'],
                'contact_person' => $validatedData['tb_contactPerson'],
                'updated_by' => $userId,
            ]);

            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
        } catch (ValidationException $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while updating the supplier. Please try again.')->withInput();
        }
    }

    public function deleteSupplier($id)
    {
        try {
            DB::beginTransaction();

            SupplierMaster::where('supplier_id', $id)
                ->update([
                    'status' => '0',
                    'updated_by' => request()->cookie('GTA'),
                    'deleted_at' => now(),
                ]);
            DB::commit();

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}