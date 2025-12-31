<?php

namespace App\Integrations\Uanataca\Requests;

class IssuanceRequest
{
    public static function build(CertificateRequestDTO $dto): array
    {
        return [
            'id' => $dto->externalId,
            'subject' => [
                'cn' => $dto->commonName,
                'email' => $dto->email,
                'serialNumber' => $dto->documentNumber,
                'c' => $dto->countryCode
            ],
        ];
    }
}