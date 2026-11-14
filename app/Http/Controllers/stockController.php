<?php

namespace App\Http\Controllers;

use App\Models\ProductMaster;
use App\Services\InventoryLedger;
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

    public function movements(Request $request, int $productId)
    {
        $userId = (int) $request->attributes->get('authenticated_user_id');
        $product = ProductMaster::query()
            ->where('created_by', $userId)
            ->findOrFail($productId);
        $movements = DB::table('inventory_movements')
            ->where('product_id', $productId)
            ->orderByDesc('inventory_movement_id')
            ->paginate(20)
            ->withQueryString();

        return view('stocks.movements', compact('product', 'movements'));
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

        app(InventoryLedger::class)->setOnHand(
            (int) $product->product_id,
            (int) $validatedData['quantity_in_stock'],
            (int) $userId
        );

        return redirect()
            ->route('stock.index', $request->only('search'))
            ->with('success', 'Stock quantity updated successfully.');
    }
}
