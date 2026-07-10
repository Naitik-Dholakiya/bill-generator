<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierMaster extends Model
{
    protected $table = 'suppliermaster';

    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'contact_person',
        'email',
        'phone',
        'gst_number',
        'billing_address',
        'shipping_address',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * User who created the supplier
     */
    public function creator()
    {
        return $this->belongsTo(Usermaster::class, 'created_by', 'user_id');
    }

    /**
     * User who last updated the supplier
     */
    public function updater()
    {
        return $this->belongsTo(Usermaster::class, 'updated_by', 'user_id');
    }
}