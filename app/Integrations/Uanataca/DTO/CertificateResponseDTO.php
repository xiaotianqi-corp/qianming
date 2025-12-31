<?php

namespace App\Integrations\Uanataca\DTO;

class CertificateResponseDTO 
{
    public function __construct(
        public bool $success,
        public ?string $orderId = null,
        public ?string $status = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public ?array $rawResponse = null
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            success: isset($data['id']),
            orderId: $data['id'] ?? null,
            status: $data['status'] ?? 'submitted',
            errorCode: $data['error']['code'] ?? null,
            errorMessage: $data['error']['message'] ?? null,
            rawResponse: $data
        );
    }
}