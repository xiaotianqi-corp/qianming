<?php

namespace App\Integrations\Uanataca\DTO;

class PaymentResult {
    public function __construct(
        public bool $success,
        public string $orderId,
        public ?string $transactionId = null,
        public ?array $rawPayload = null,
        protected ?array $data = []
    ) {}

    public function successful(): bool { return $this->success; }
    public function orderId(): string { return $this->orderId; }
}