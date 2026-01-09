<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'active',
        'is_default',
        'first_response_time',
        'resolution_time',
        'conditions',
        'business_hours_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_default' => 'boolean',
        'conditions' => 'array',
    ];

    public function businessHours(): BelongsTo
    {
        return $this->belongsTo(BusinessHours::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'sla_policy_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
