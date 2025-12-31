<?php

namespace App\Integrations\Uanataca\Responses;

use Illuminate\Http\Client\Response;

class UanatacaApiResponse
{
    public function __construct(
        protected Response $response
    ) {}

    public function isSuccessful(): bool
    {
        return $this->response->successful() && isset($this->response->json()['id']);
    }

    public function getExternalId(): ?string
    {
        return $this->response->json('id');
    }

    public function getStatus(): string
    {
        return $this->response->json('status', 'unknown');
    }

    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) return null;

        return $this->response->json('error.message') 
               ?? $this->response->json('message') 
               ?? 'Unknown Error from PSC';
    }

    public function getData(): array
    {
        return $this->response->json();
    }
}