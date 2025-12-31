<?php

namespace App\Integrations\Uanataca\Mappers;

use App\Models\Identity;
use App\Models\Order;
use App\Integrations\Uanataca\DTO\CertificateRequestDTO;

class IdentityToUanatacaMapper {
    public static function map(Identity $identity, Order $order): CertificateRequestDTO {
        return new CertificateRequestDTO(
            externalId: "ORD-{$order->id}",
            commonName: "{$identity->first_name} {$identity->last_name}",
            email: $identity->email,
            documentNumber: $identity->document_number,
            countryCode: $identity->country->code,
            validityYears: $order->items()->first()->product->validity_years
        );
    }
}