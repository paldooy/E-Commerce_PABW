<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'nama_produk_snapshot',
        'deskripsi_produk_snapshot',
        'gambar_produk_snapshot',
        'harga_satuan_snapshot',
        'jumlah',
        'subtotal',
        'status',
        'alasan_komplain',
        'failed_at',
    ];

    protected $casts = [
        'harga_satuan_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'jumlah' => 'integer',
        'failed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'seller_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderItemStatusLog::class, 'order_item_id');
    }
}
