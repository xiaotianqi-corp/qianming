<?php

namespace App\Integrations\Uanataca\Requests;

use App\Integrations\Uanataca\DTO\CertificateRequestDTO;

class CertificateIssuanceRequest
{
    public function __construct(
        protected CertificateRequestDTO $dto
    ) {}

    public function payload(): array
    {
        return [
            'external_id' => $this->dto->externalId,
            'subject' => [
                'common_name' => $this->dto->commonName,
                'email'       => $this->dto->email,
                'country'     => $this->dto->countryCode,
                'serial_number' => $this->dto->documentNumber,
            ],
            'validity' => [
                'years' => $this->dto->validityYears
            ],
            'type' => 'cloud_signature', 
            'notification_url' => config('services.uanataca.webhook_url'),
        ];
    }
}