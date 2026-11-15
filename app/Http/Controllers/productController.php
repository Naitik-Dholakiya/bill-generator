<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductMaster;
use App\Models\SupplierMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class productController extends Controller
{
    public function index()
    {
        $userId = (int) request()->attributes->get('authenticated_user_id');
        $products = ProductMaster::where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->with(['category', 'supplier'])
            ->get();
        $categories = Category::where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->get();
        $productss = $products;
        $isJewelryUser = $this->isJewelryUser($userId);

        return view('products.productIndex', compact('products', 'categories', 'productss', 'isJewelryUser'));
    }

    public function storeAjaxCategory(Request $request)
    {
        $userId = (int) request()->attributes->get('authenticated_user_id');

        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:100', Rule::unique('categorymaster', 'category_name')->where('created_by', $userId)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category = Category::create([
            'category_name' => $validated['category_name'],
            'description' => $validated['description'] ?? null,
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
        $userId = (int) request()->attributes->get('authenticated_user_id');

        // Find last products of this user
        $lastproducts = ProductMaster::where('created_by', $userId)
            ->orderByDesc('product_id')
            ->first();

        $nextNumber = 1;

        if ($lastproducts) {
            $lastNumber = (int) substr($lastproducts->product_code, -4);
            $nextNumber = $lastNumber + 1;
        }

        $productCode =
            'PROD'.
            str_pad($userId, 2, '0', STR_PAD_LEFT).
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $categories = Category::where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->get();
        $suppliers = SupplierMaster::where('created_by', $userId)->where('status', '1')->orderBy('supplier_name')->get();
        $isJewelryUser = $this->isJewelryUser($userId);

        return view('products.productCreate', compact('categories', 'suppliers', 'productCode', 'isJewelryUser'));
    }

    public function createProductPost(Request $request)
    {
        $userId = (int) $request->attributes->get('authenticated_user_id');
        $isJewelryUser = $this->isJewelryUser($userId);
        $rules = [
            'lf_product_code' => ['required', 'string', 'max:20', Rule::unique('productmaster', 'product_code')],
            'tb_product_name' => ['required', 'string', 'max:150', Rule::unique('productmaster', 'product_name')->where('created_by', $userId)],
            'dd_supplier' => ['required', 'integer', Rule::exists('suppliermaster', 'supplier_id')->where('created_by', $userId)->where('status', '1')->whereNull('deleted_at')],
            'dd_category' => ['required', 'integer', Rule::exists('categorymaster', 'category_id')->where('created_by', $userId)->where('status', '1')->whereNull('deleted_at')],
            'tb_purchase_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'tb_selling_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ];
        if ($isJewelryUser) {
            $rules['tb_reorder_weight_level'] = ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'];
            $rules['tb_touch'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
            $rules['tb_default_wastage'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
        } else {
            $rules['tb_reorder_level'] = ['required', 'integer', 'min:0', 'max:2147483647'];
        }

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'tb_product_name.required' => 'Product name is required.',
                'dd_supplier.required' => 'Supplier is required.',
                'dd_category.required' => 'Category is required.',
                'tb_purchase_price.required' => 'Purchase price is required.',
                'tb_selling_price.required' => 'Selling price is required.',
                'tb_reorder_level.required' => 'Reorder level is required.',
                'tb_reorder_weight_level.required' => 'Reorder level in grams is required.',
            ],
            [
                'lf_product_code' => 'Product Code',
                'tb_product_name' => 'Product Name',
                'dd_supplier' => 'Supplier',
                'dd_category' => 'Category',
                'tb_purchase_price' => 'Purchase Price',
                'tb_selling_price' => 'Selling Price',
                'tb_reorder_level' => 'Reorder Level',
                'tb_reorder_weight_level' => 'Reorder Level (g)',
            ]
        );
        
        // Check validation FIRST
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            ProductMaster::create([
                'product_code' => $validated['lf_product_code'],
                'product_name' => $validated['tb_product_name'],
                'supplier_id' => $validated['dd_supplier'],
                'category_id' => $validated['dd_category'],
                'purchase_price' => $validated['tb_purchase_price'],
                'selling_price' => $validated['tb_selling_price'],
                'touch' => $isJewelryUser ? ($validated['tb_touch'] ?? null) : null,
                'default_wastage' => $isJewelryUser ? ($validated['tb_default_wastage'] ?? null) : null,
                'reorder_level' => $isJewelryUser ? 0 : $validated['tb_reorder_level'],
                'reorder_weight_level' => $isJewelryUser ? $validated['tb_reorder_weight_level'] : 0,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()
                ->back()
                ->with('error', 'Unable to save the product. Please try again.')
                ->withInput();
        }
    }

    public function viewProduct($id)
    {
        $userId = (int) request()->attributes->get('authenticated_user_id');
        $product = ProductMaster::where('created_by', $userId)->findOrFail($id);
        $isJewelryUser = $this->isJewelryUser($userId);

        return view('products.productView', compact('product', 'isJewelryUser'));
    }

    public function editProduct($id)
    {
        $userId = (int) request()->attributes->get('authenticated_user_id');
        $product = ProductMaster::where('created_by', $userId)->findOrFail($id);
        $categories = Category::where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->get();
        $suppliers = SupplierMaster::where('created_by', $userId)->where('status', '1')->orderBy('supplier_name')->get();
        $isJewelryUser = $this->isJewelryUser($userId);

        return view('products.productEdit', compact('product', 'categories', 'suppliers', 'isJewelryUser'));
    }

    public function editProductPost(Request $request, $id)
    {
        try {
            $userId = (int) $request->attributes->get('authenticated_user_id');
            $isJewelryUser = $this->isJewelryUser($userId);
            $rules = [
                'lf_product_code' => ['required', 'string', 'max:20', Rule::unique('productmaster', 'product_code')->ignore($id, 'product_id')],
                'tb_product_name' => ['required', 'string', 'max:150', Rule::unique('productmaster', 'product_name')->where('created_by', $userId)->ignore($id, 'product_id')],
                'dd_supplier' => ['required', 'integer', Rule::exists('suppliermaster', 'supplier_id')->where('created_by', $userId)->where('status', '1')->whereNull('deleted_at')],
                'dd_category' => ['required', 'integer', Rule::exists('categorymaster', 'category_id')->where('created_by', $userId)->where('status', '1')->whereNull('deleted_at')],
                'tb_purchase_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
                'tb_selling_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
                'tb_status' => ['required', Rule::in(['0', '1'])],
            ];
            if ($isJewelryUser) {
                $rules['tb_reorder_weight_level'] = ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'];
                $rules['tb_touch'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
                $rules['tb_default_wastage'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
            } else {
                $rules['tb_reorder_level'] = ['required', 'integer', 'min:0', 'max:2147483647'];
            }

            $validator = Validator::make($request->all(), $rules, [
                'tb_product_name.required' => 'Product name is required.',
                'dd_supplier.required' => 'Supplier is required.',
                'dd_category.required' => 'Category is required.',
                'tb_purchase_price.required' => 'Purchase price is required.',
                'tb_selling_price.required' => 'Selling price is required.',
                'tb_reorder_level.required' => 'Reorder level is required.',
                'tb_reorder_weight_level.required' => 'Reorder level in grams is required.',
                'tb_status.required' => 'Status is required.',
            ], [
                'lf_product_code' => 'Product Code',
                'tb_product_name' => 'Product Name',
                'dd_supplier' => 'Supplier',
                'dd_category' => 'Category',
                'tb_purchase_price' => 'Purchase Price',
                'tb_selling_price' => 'Selling Price',
                'tb_reorder_level' => 'Reorder Level',
                'tb_reorder_weight_level' => 'Reorder Level (g)',
                'tb_status' => 'Status',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $product = ProductMaster::where('created_by', $userId)->findOrFail($id);
            $validatedData = $validator->validated();

            DB::beginTransaction();
            $updates = [
                'product_code' => $validatedData['lf_product_code'],
                'product_name' => $validatedData['tb_product_name'],
                'supplier_id' => $validatedData['dd_supplier'],
                'category_id' => $validatedData['dd_category'],
                'purchase_price' => $validatedData['tb_purchase_price'],
                'selling_price' => $validatedData['tb_selling_price'],
                'reorder_level' => $isJewelryUser ? 0 : $validatedData['tb_reorder_level'],
                'reorder_weight_level' => $isJewelryUser ? $validatedData['tb_reorder_weight_level'] : 0,
                'status' => $validatedData['tb_status'],
                'updated_by' => $userId,
                'updated_at' => now(),
            ];
            if ($isJewelryUser) {
                $updates['touch'] = $validatedData['tb_touch'] ?? null;
                $updates['default_wastage'] = $validatedData['tb_default_wastage'] ?? null;
            }
            $product->update($updates);
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
            return redirect()->route('products.index')->with('error', 'Unable to delete the product. Please try again.');
        }
    }

    private function isJewelryUser($userId): bool
    {
        return (bool) DB::table('usermaster')
            ->where('user_id', $userId)
            ->value('is_jewelry');
    }
}
