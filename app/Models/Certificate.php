<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'order_item_id',
        'provider',
        'status',
        'external_id',
        'issued_at',
        'expires_at',
        'revoked_at',
        'payload',
        'renewed_from_id'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'payload' => 'array',
        'status' => CertificateStatus::class,
    ];

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }
}