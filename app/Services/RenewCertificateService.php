<?php

namespace App\Services;

use App\Models\SignatureProduct;
use App\Models\Country;
use App\Exceptions\ProductNotAvailableInCountryException;

class CountryAvailabilityService
{
    public function renew(CertificateRequest $certificate)
    {
        if (! $certificate->identity->verified) {
            throw new IdentityNotVerifiedException();
        }

        return Order::createRenewalFrom($certificate);
    }
}