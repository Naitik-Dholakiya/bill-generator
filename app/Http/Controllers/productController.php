<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductMaster;
use App\Models\SupplierMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class productController extends Controller
{
    public function index()
    {
        $products = ProductMaster::all();
        $categories = Category::all();
        $productss = ProductMaster::all();

        return view('products.productIndex', compact('products', 'categories', 'productss'));
    }

    public function storeAjaxCategory(Request $request)
    {
        $userId = request()->cookies->get('GTA');

        $request->validate([
            'category_name' => 'required|max:100|unique:categorymaster,category_name',
            'description' => 'nullable|max:255',
        ]);

        $category = Category::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => '1',
            'created_by' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category Added Successfully',
            'category' => $category,
        ]);
    }

    public function createProduct()
    {
        $userId = request()->cookies->get('GTA');

        // Find last products of this user
        $lastproducts = ProductMaster::where('created_by', $userId)
            ->orderByDesc('product_id')
            ->first();

        $nextNumber = 1;

        if ($lastproducts) {
            $lastNumber = (int) substr($lastproducts->products_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        $productCode =
            'PROD'.
            str_pad($userId, 2, '0', STR_PAD_LEFT).
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $categories = Category::orderBy('category_name')->get();
        $suppliers = SupplierMaster::orderBy('supplier_name')->get();

        return view('products.productCreate', compact('categories', 'suppliers', 'productCode'));
    }

    public function createProductPost(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'lf_product_code' => 'required|max:20|unique:productmaster,product_code',
                'tb_product_name' => 'required|max:100',
                'dd_supplier' => 'required|exists:suppliermaster,supplier_id',
                'dd_category' => 'required|exists:categorymaster,category_id',
                'tb_purchase_price' => 'required|numeric|min:0',
                'tb_selling_price' => 'required|numeric|min:0',
                'tb_reorder_level' => 'required|numeric|min:0',
            ],
            [
                'tb_product_name.required' => 'Product name is required.',
                'dd_supplier.required' => 'Supplier is required.',
                'dd_category.required' => 'Category is required.',
                'tb_purchase_price.required' => 'Purchase price is required.',
                'tb_selling_price.required' => 'Selling price is required.',
                'tb_reorder_level.required' => 'Reorder level is required.',
            ],
            [
                'lf_product_code' => 'Product Code',
                'tb_product_name' => 'Product Name',
                'dd_supplier' => 'Supplier',
                'dd_category' => 'Category',
                'tb_purchase_price' => 'Purchase Price',
                'tb_selling_price' => 'Selling Price',
                'tb_reorder_level' => 'Reorder Level',
            ]
        );
        
        // Check validation FIRST
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', $validator->errors()->all())
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $userId = request()->cookies->get('GTA');
            $validated = $validator->validated();

            ProductMaster::create([
                'product_code' => $validated['lf_product_code'],
                'product_name' => $validated['tb_product_name'],
                'supplier_id' => $validated['dd_supplier'],
                'category_id' => $validated['dd_category'],
                'purchase_price' => $validated['tb_purchase_price'],
                'selling_price' => $validated['tb_selling_price'],
                'reorder_level' => $validated['tb_reorder_level'],
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating product: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('Warning', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    public function viewProduct($id)
    {
        $product = ProductMaster::findOrFail($id);
        return view('products.productView', compact('product'));
    }

    public function editProduct($id)
    {
        $product = ProductMaster::findOrFail($id);
        $categories = Category::orderBy('category_name')->get();
        $suppliers = SupplierMaster::orderBy('supplier_name')->get();

        return view('products.productEdit', compact('product', 'categories', 'suppliers'));
    }

    public function editProductPost(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lf_product_code' => 'required|max:20|unique:productmaster,product_code,'.$id.',product_id',
                'tb_product_name' => 'required|max:100',
                'dd_supplier' => 'required|exists:suppliermaster,supplier_id',
                'dd_category' => 'required|exists:categorymaster,category_id',
                'tb_purchase_price' => 'required|numeric|min:0',
                'tb_selling_price' => 'required|numeric|min:0',
                'tb_reorder_level' => 'required|numeric|min:0',
                'tb_status' => 'required|in:0,1',
            ], [
                'tb_product_name.required' => 'Product name is required.',
                'dd_supplier.required' => 'Supplier is required.',
                'dd_category.required' => 'Category is required.',
                'tb_purchase_price.required' => 'Purchase price is required.',
                'tb_selling_price.required' => 'Selling price is required.',
                'tb_reorder_level.required' => 'Reorder level is required.',
                'tb_status.required' => 'Status is required.',
            ], [
                'lf_product_code' => 'Product Code',
                'tb_product_name' => 'Product Name',
                'dd_supplier' => 'Supplier',
                'dd_category' => 'Category',
                'tb_purchase_price' => 'Purchase Price',
                'tb_selling_price' => 'Selling Price',
                'tb_reorder_level' => 'Reorder Level',
                'tb_status' => 'Status',
            ]);

            $userId = request()->cookies->get('GTA');
            $product = ProductMaster::findOrFail($id);
            $validatedData = $validator->validated();

            DB::beginTransaction();
            $product->update([
                'product_code' => $validatedData['lf_product_code'],
                'product_name' => $validatedData['tb_product_name'],
                'supplier_id' => $validatedData['dd_supplier'],
                'category_id' => $validatedData['dd_category'],
                'purchase_price' => $validatedData['tb_purchase_price'],
                'selling_price' => $validatedData['tb_selling_price'],
                'reorder_level' => $validatedData['tb_reorder_level'],
                'status' => $validatedData['tb_status'],
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors())->withInput();
            }
            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating product: '.$e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.Please Try Again.')->withInput();
        }
    }

    public function deleteProduct($id)
    {
        try {
            $product = ProductMaster::findOrFail($id);

            DB::beginTransaction();

            ProductMaster::where('product_id', $id)
                ->update([
                    'status' => '0',
                    'updated_by' => request()->cookie('GTA'),
                    'deleted_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting product: '.$e->getMessage());
            return redirect()->route('products.index')->with('error', $e->getMessage());
        }
    }
}
