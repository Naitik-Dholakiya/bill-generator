<?php

namespace App\Services;

use App\Models\InventoryMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryLedger
{
    public function applyMovement(
        int $productId,
        int $quantityDelta,
        int $userId,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        bool $allowNegative = false
    ): void {
        if ($quantityDelta === 0) {
            return;
        }

        $inventory = InventoryMaster::withTrashed()
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        $currentQuantity = $inventory && ! $inventory->trashed()
            ? (int) $inventory->quantity_in_stock
            : 0;
        $newQuantity = $currentQuantity + $quantityDelta;

        if (($newQuantity < 0 && ! $allowNegative) || $newQuantity > 2147483647 || $newQuantity < -2147483648) {
            throw ValidationException::withMessages([
                'stock' => 'This change would put the stock quantity outside its supported range.',
            ]);
        }

        if (! $inventory) {
            $inventory = InventoryMaster::create([
                'product_id' => $productId,
                'quantity_in_stock' => $newQuantity,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        } else {
            if ($inventory->trashed()) {
                $inventory->restore();
            }

            $inventory->quantity_in_stock = $newQuantity;
            $inventory->updated_by = $userId;
            $inventory->save();
        }

        DB::table('inventory_movements')->insert([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity_delta' => $quantityDelta,
            'quantity_after' => $newQuantity,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function setOnHand(int $productId, int $quantity, int $userId): void
    {
        DB::transaction(function () use ($productId, $quantity, $userId): void {
            $inventory = InventoryMaster::withTrashed()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            $currentQuantity = $inventory && ! $inventory->trashed()
                ? (int) $inventory->quantity_in_stock
                : 0;

            $this->applyMovement(
                $productId,
                $quantity - $currentQuantity,
                $userId,
                'adjustment',
                'manual',
                $productId,
                'Manual stock quantity adjustment.'
            );
        });
    }
}
