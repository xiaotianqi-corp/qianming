<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\Country;
use App\Models\ProviderCountryConfig;
use App\Exceptions\InvalidDocumentTypeException;

class IdentityRequirementService
{
    public function validate(Identity $identity, Country $country): void
    {
        $config = ProviderCountryConfig::for('uanataca', $country);
        $allowedTypes = $config->requirements['document_types'] ?? [];

        if (! in_array($identity->document_type, $allowedTypes)) {
            throw new InvalidDocumentTypeException();
        }
    }
}