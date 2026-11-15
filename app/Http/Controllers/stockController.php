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
        $isJewelryUser = (bool) DB::table('usermaster')
            ->where('user_id', $userId)
            ->value('is_jewelry');
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

        return view('stocks.stockIndex', compact('products', 'isJewelryUser'));
    }

    public function movements(Request $request, int $productId)
    {
        $userId = (int) $request->attributes->get('authenticated_user_id');
        $isJewelryUser = (bool) DB::table('usermaster')
            ->where('user_id', $userId)
            ->value('is_jewelry');
        $product = ProductMaster::query()
            ->where('created_by', $userId)
            ->findOrFail($productId);
        $movements = DB::table('inventory_movements')
            ->where('product_id', $productId)
            ->when($isJewelryUser, function ($query): void {
                $query->where(function ($weightQuery): void {
                    $weightQuery->whereNotNull('weight_delta')
                        ->orWhereNotNull('weight_after');
                });
            })
            ->orderByDesc('inventory_movement_id')
            ->paginate(20)
            ->withQueryString();

        return view('stocks.movements', compact('product', 'movements', 'isJewelryUser'));
    }

    public function updateStock(Request $request, int $productId)
    {
        $product = ProductMaster::query()
            ->where('created_by', $request->attributes->get('authenticated_user_id'))
            ->findOrFail($productId);

        $userId = (int) $request->attributes->get('authenticated_user_id');
        $isJewelryUser = (bool) DB::table('usermaster')
            ->where('user_id', $userId)
            ->value('is_jewelry');

        if ($isJewelryUser) {
            $validated = $request->validate([
                'weight_in_stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'],
            ]);
            app(InventoryLedger::class)->setWeightOnHand(
                (int) $product->product_id,
                (float) $validated['weight_in_stock'],
                $userId
            );
        } else {
            $validated = $request->validate([
                'quantity_in_stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ]);
            app(InventoryLedger::class)->setOnHand(
                (int) $product->product_id,
                (int) $validated['quantity_in_stock'],
                $userId
            );
        }

        return redirect()
            ->route('stock.index', $request->only('search'))
            ->with('success', $isJewelryUser ? 'Net-weight stock updated successfully.' : 'Stock quantity updated successfully.');
    }
}
