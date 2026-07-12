<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductMaster;
use App\Models\SupplierMaster;
use Illuminate\Http\Request;

class productController extends Controller
{
    public function index()
    {
        $products = ProductMaster::with(['category', 'products', 'creator', 'updater'])->get();
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

        return response()->with([
            'success' => true,
            'message' => 'Category added successfully.',
            'category' => [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
            ],
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
}
