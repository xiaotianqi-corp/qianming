<?php

namespace App\Integrations\Uanataca;

use App\Integrations\Uanataca\DTO\CertificateRequestDTO;
use App\Integrations\Uanataca\DTO\CertificateResponseDTO;
use App\Integrations\Uanataca\Requests\CertificateIssuanceRequest;
use App\Integrations\Uanataca\Responses\UanatacaApiResponse;
use Illuminate\Support\Facades\Http;

class UanatacaClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.uanataca.base_url');
        $this->apiKey = config('services.uanataca.api_key');
    }

    public function requestCertificate(CertificateRequestDTO $dto): UanatacaApiResponse
    {
        $request = new CertificateIssuanceRequest($dto);

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/requests", $dto->payload());

        return new UanatacaApiResponse($response);
    }

    public function downloadBinary(string $externalId): string
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/requests/{$externalId}/download");

        if (!$response->successful()) {
            throw new \Exception("The Uanataca certificate could not be downloaded: " . $response->body());
        }

        return $response->body();
    }
}