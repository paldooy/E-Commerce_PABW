<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OrderItemStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id',
        'status_lama',
        'status_baru',
        'diubah_oleh_account_id',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'diubah_oleh_account_id');
    }
}
