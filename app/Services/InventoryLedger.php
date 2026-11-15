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

    public function applyWeightMovement(
        int $productId,
        float $weightDelta,
        int $userId,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        bool $allowNegative = false
    ): float {
        $deltaMilli = (int) round($weightDelta * 1000);
        if ($deltaMilli === 0) {
            return (float) InventoryMaster::withTrashed()
                ->where('product_id', $productId)
                ->value('weight_in_stock');
        }

        $inventory = InventoryMaster::withTrashed()
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        $currentMilli = $inventory && ! $inventory->trashed()
            ? (int) round((float) $inventory->weight_in_stock * 1000)
            : 0;
        $newMilli = $currentMilli + $deltaMilli;
        $maximumMilli = 999999999999;

        if ((! $allowNegative && $newMilli < 0) || $newMilli > $maximumMilli || $newMilli < -$maximumMilli) {
            throw ValidationException::withMessages([
                'stock' => 'This change would make net-weight stock negative or exceed its supported range.',
            ]);
        }

        $newWeight = number_format($newMilli / 1000, 3, '.', '');
        if (! $inventory) {
            $inventory = InventoryMaster::create([
                'product_id' => $productId,
                'quantity_in_stock' => 0,
                'weight_in_stock' => $newWeight,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        } else {
            if ($inventory->trashed()) {
                $inventory->restore();
            }

            $inventory->weight_in_stock = $newWeight;
            $inventory->updated_by = $userId;
            $inventory->save();
        }

        DB::table('inventory_movements')->insert([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity_delta' => 0,
            'quantity_after' => (int) ($inventory->quantity_in_stock ?? 0),
            'weight_delta' => number_format($deltaMilli / 1000, 3, '.', ''),
            'weight_after' => $newWeight,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (float) $newWeight;
    }

    public function setWeightOnHand(int $productId, float $weight, int $userId): void
    {
        DB::transaction(function () use ($productId, $weight, $userId): void {
            if (! is_finite($weight) || $weight < 0 || $weight > 999999999.999) {
                throw ValidationException::withMessages([
                    'weight_in_stock' => 'Enter a valid net-weight balance in grams.',
                ]);
            }

            $inventory = InventoryMaster::withTrashed()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            $currentWeight = $inventory && ! $inventory->trashed()
                ? (float) $inventory->weight_in_stock
                : 0;

            $this->applyWeightMovement(
                $productId,
                round($weight - $currentWeight, 3),
                $userId,
                'adjustment',
                'manual',
                $productId,
                'Manual net-weight stock adjustment.'
            );
        });
    }
}
