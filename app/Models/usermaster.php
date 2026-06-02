<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class usermaster extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'usermaster';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'role_id',
        'full_name',
        'email',
        'password',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User belongs to a Role
    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    // User creates many invoices
    // public function invoices()
    // {
    //     return $this->hasMany(Invoice::class);
    // }

    // // User creates many purchases
    // public function purchases()
    // {
    //     return $this->hasMany(Purchase::class);
    // }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
