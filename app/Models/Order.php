<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'nomor_pesanan',
        'buyer_id',
        'total_harga',
        'escrow_status',
        'paid_at',
        'released_at',
        'refunded_at',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'buyer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
