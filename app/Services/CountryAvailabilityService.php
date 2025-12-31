<?php

namespace App\Services;

use App\Models\SignatureProduct;
use App\Models\Country;
use App\Exceptions\ProductNotAvailableInCountryException;

class CountryAvailabilityService
{
    public function validate(SignatureProduct $product, Country $country): void
    {
        if (! $product->isAvailableIn($country)) {
            throw new ProductNotAvailableInCountryException();
        }
    }
}