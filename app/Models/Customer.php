<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\usermaster;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customermaster';
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_code',
        'customer_name',
        'email',
        'phone',
        'gst_number',
        'billing_address',
        'shipping_address',
        'status',
        'created_by',
        'updated_by',
    ];
}