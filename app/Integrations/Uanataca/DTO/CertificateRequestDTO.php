<?php

namespace App\Integrations\Uanataca\DTO;

class CertificateRequestDTO {
    public function __construct(
        public string $externalId,
        public string $commonName,
        public string $email,
        public string $documentNumber,
        public string $countryCode,
        public int $validityYears
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->externalId,
            'subject' => [
                'cn' => $this->commonName,
                'email' => $this->email,
                'dn' => $this->documentNumber,
                'c' => $this->countryCode
            ],
            'validity' => $this->validityYears
        ];
    }
}