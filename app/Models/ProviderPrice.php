<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderPrice extends Model
{
    
    protected $fillable = [
        'provider_country_config_id',
        'signature_product_id',
        'range',
        'min',
        'max',
        'provider_price',
    ];

    public function providerCountryConfig(): BelongsTo
    {
        return $this->belongsTo(ProviderCountryConfig::class, 'provider_country_config_id');
    }
}
