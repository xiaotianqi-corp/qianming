<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderCountryConfig extends Model
{

    protected $fillable = [
        'provider_id',
        'country_id',
        'requirements',
        'active',
    ];
    
    public function provider()
    { 
        return $this->belongsTo(Provider::class); 
    }

    public function country()
    { 
        return $this->belongsTo(Country::class); 
    }
    
    public function prices()
    { 
        return $this->hasMany(ProviderPrice::class); 
    }

    public static function scopeFor($query, string $providerIdentifier, Country $country)
    {
        return $query->whereHas('provider', function ($q) use ($providerIdentifier) {
            $q->where('identifier', $providerIdentifier);
        })->where('country_id', $country->id)->firstOrFail();
    }
}
