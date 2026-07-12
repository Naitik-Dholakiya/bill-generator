<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categorymaster';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Usermaster::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(Usermaster::class, 'updated_by', 'user_id');
    }
}