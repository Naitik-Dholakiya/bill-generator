<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMaster extends Model
{
    protected $table = 'productmaster';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_code',
        'product_name',
        'category_id',
        'supplier_id',
        // 'product_internal_code',
        // 'barcode',
        'purchase_price',
        'selling_price',
        'reorder_level',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierMaster::class, 'supplier_id', 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(Usermaster::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(Usermaster::class, 'updated_by', 'user_id');
    }
}