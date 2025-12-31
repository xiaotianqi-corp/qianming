<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialDocument extends Model
{
    protected $fillable = [
        'order_id',
        'country_id',
        'type',
        'number',
        'subtotal',
        'tax',
        'total',
        'currency',
        'status',
        'external_id',
        'pdf_url',
        'xml_url'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la Orden.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación con el País.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}