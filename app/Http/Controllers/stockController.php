<?php

namespace App\Http\Controllers;

use App\Models\InventoryMaster;
use App\Models\ProductMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class stockController extends Controller
{
    public function index()
    {
        $userId = request()->attributes->get('authenticated_user_id');
        $search = request('search');

        $products = ProductMaster::query()
            ->where('created_by', $userId)
            ->where('status', '1')
            ->with(['inventory', 'category'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('product_code', 'like', '%'.$search.'%')
                        ->orWhere('product_name', 'like', '%'.$search.'%')
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('category_name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderBy('product_name')
            ->paginate(10)
            ->withQueryString();

        return view('stocks.stockIndex', compact('products'));
    }

    public function updateStock(Request $request, int $productId)
    {
        $validatedData = $request->validate([
            'quantity_in_stock' => 'required|integer|min:0|max:2147483647',
        ]);

        $product = ProductMaster::query()
            ->where('created_by', $request->attributes->get('authenticated_user_id'))
            ->findOrFail($productId);

        $userId = $request->attributes->get('authenticated_user_id');

        DB::transaction(function () use ($product, $validatedData, $userId) {
            $inventory = InventoryMaster::withTrashed()->updateOrCreate(
                ['product_id' => $product->product_id],
                [
                    'quantity_in_stock' => $validatedData['quantity_in_stock'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );

            if ($inventory->trashed()) {
                $inventory->restore();
            }
        });

        return redirect()
            ->route('stock.index', $request->only('search'))
            ->with('success', 'Stock quantity updated successfully.');
    }
}
