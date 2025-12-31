<?php

namespace App\Services;

use App\Models\SignatureProduct;

class PricingService
{
    public function resolveProviderCost(
        SignatureProduct $product,
        int $monthlyVolume,
        int $providerId,
        int $countryId
    ): float {
        $range = $monthlyVolume <= 100 ? 'A' : 'B';

        return $product->priceRanges() 
            ->where('range', $range)
            ->whereHas('providerCountryConfig', function ($query) use ($providerId, $countryId) {
                $query->where('provider_id', $providerId)
                      ->where('country_id', $countryId);
            })
            ->firstOrFail()
            ->provider_price;
    }
}