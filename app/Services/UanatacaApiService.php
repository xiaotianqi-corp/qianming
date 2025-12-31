<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\CertificateRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UanatacaApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.uanataca.base_url', 'https://api.uanataca.com/v1');
        $this->apiKey = config('services.uanataca.api_key');
    }
    
    public function requestCertificate(Identity $identity, CertificateRequest $request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])
        ->timeout(30) 
        ->post("{$this->baseUrl}/certificates/issue", [
            'external_id' => "REQ-{$request->id}",
            'subject' => [
                'given_name'  => $identity->first_name,
                'family_name' => $identity->last_name,
                'email'       => $identity->email,
                'country'     => $identity->country->code,
                'serial_number' => $identity->document_number,
            ],
            'validity_years' => $request->orderItem->product->validity_years,
        ]);

        if ($response->failed()) {
            Log::error("Communication error with Uanataca: " . $response->body());
        }

        return $response;
    }

    public function checkStatus(string $externalId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get("{$this->baseUrl}/certificates/status/{$externalId}");

        if ($response->successful()) {
            return $response->json('status');
        }

        return null;
    }
}