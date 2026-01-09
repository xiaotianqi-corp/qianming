<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessHours extends Model
{
    protected $fillable = [
        'name',
        'description',
        'timezone',
        'is_default',
        'schedule',
        'holidays',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'schedule' => 'array',
        'holidays' => 'array',
    ];

    public function slaPolicies(): HasMany
    {
        return $this->hasMany(SlaPolicy::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isWorkingDay(\DateTime $date): bool
    {
        $holidays = $this->holidays ?? [];
        $dateStr = $date->format('Y-m-d');
        
        return !in_array($dateStr, $holidays);
    }

    public function getWorkingHours(string $day): ?array
    {
        return $this->schedule[$day] ?? null;
    }
}
