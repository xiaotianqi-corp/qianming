<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'code',
        'name',
        'currency',
        'active'
    ];

    public function providerConfigs(): HasMany
    {
        return $this->hasMany(ProviderCountryConfig::class);
    }

    public function identities(): HasMany
    {
        return $this->hasMany(Identity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}