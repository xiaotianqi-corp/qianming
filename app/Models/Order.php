<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'status',
        'total',
        'payment_status'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];
    
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
