<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMaster extends Model
{
    use SoftDeletes;

    protected $table = 'inventorymaster';

    protected $primaryKey = 'inventory_id';

    protected $fillable = [
        'product_id',
        'quantity_in_stock',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity_in_stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class, 'product_id', 'product_id');
    }
}
