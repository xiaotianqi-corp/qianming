<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{

    protected $table = 'signature_products';

    protected $fillable = [
        'code',
        'container',
        'validity_years',
        'subscriber_type',
        'pvp',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'pvp' => 'decimal:2'
    ];

    public function priceRanges(): HasMany
    {
        return $this->hasMany(ProviderPrice::class, 'signature_product_id');
    }

    public function isAvailableIn(Country $country): bool
    {
        return $this->priceRanges()
            ->whereHas('providerCountryConfig', function ($query) use ($country) {
                $query->where('country_id', $country->id)
                      ->where('active', true);
            })->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}