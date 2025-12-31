<?php

namespace App\Jobs;

use App\Models\CertificateRequest;
use App\Models\Certificate;
use App\Models\AuditEvent;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Support\Facades\Storage;
use App\Integrations\Uanataca\UanatacaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DownloadCertificateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CertificateRequest $certRequest, 
        protected array $payload
    ) {}

    public function handle(UanatacaClient $client)
    {
        $binary = $client->downloadBinary($this->certRequest->external_id);
    
        $path = "certificates/" . $this->certRequest->orderItem->order->user_id . "/" . uniqid() . ".p12";
        Storage::disk('s3')->put($path, $binary);
    
        $certificate = Certificate::create([
            'order_item_id' => $this->certRequest->order_item_id,
            'status'        => 'active',
            'path'          => $path,
            'external_id'   => $this->certRequest->external_id,
            'issued_at'     => now(),
            'expires_at'    => now()->addYears(1),
        ]);
    
        $user = $this->certRequest->orderItem->order->user;
        
        $user->notify(new CertificateIssuedNotification($certificate));
    
        AuditEvent::log('certificate_delivery_completed', [
            'certificate_id' => $certificate->id,
            'user_id' => $user->id
        ]);
    }
}