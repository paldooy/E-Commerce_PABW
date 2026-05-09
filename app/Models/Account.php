<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'role',
        'nama_lengkap',
        'username',
        'email',
        'no_hp',
        'password_hash',
        'status_aktif',
        'remember_token',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'account_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'buyer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function soldItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'seller_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderItemStatusLog::class, 'diubah_oleh_account_id');
    }
}
