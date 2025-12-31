<?php

namespace App\Jobs;

use App\Models\CertificateRequest;
use App\Models\AuditEvent;
use App\Enums\IdentityStatus;
use App\Exceptions\IdentityNotVerifiedException;
use App\Integrations\Uanataca\UanatacaClient;
use App\Integrations\Uanataca\Mappers\IdentityToUanatacaMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IssueCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900];

    public function __construct(protected CertificateRequest $certificateRequest) 
    {
    }

    public function handle(UanatacaClient $client): void
    {
        $order = $this->certificateRequest->orderItem->order;
        $identity = $order->identity;

        if ($identity->status !== IdentityStatus::VERIFIED) {
            $this->fail(new IdentityNotVerifiedException(
                "Identity {$identity->id} it must be verified before issuing."
            ));
            return;
        }

        $requestDTO = IdentityToUanatacaMapper::map($identity, $order);

        try {
            $responseDTO = $client->requestCertificate($requestDTO);

            if ($responseDTO->success) {
                $this->certificateRequest->update([
                    'status' => 'submitted',
                    'external_id' => $responseDTO->orderId,
                    'payload' => $responseDTO->rawResponse
                ]);

                AuditEvent::log('psc_issuance_submitted', [
                    'certificate_request_id' => $this->certificateRequest->id,
                    'external_order_id' => $responseDTO->orderId
                ]);

            } else {
                $this->certificateRequest->update([
                    'status' => 'failed',
                    'error_log' => $responseDTO->errorMessage
                ]);

                Log::error("Uanataca Error in Request {$this->certificateRequest->id}: " . $responseDTO->errorMessage);
            }

        } catch (\Exception $e) {
            Log::warning("Temporary connection failure with Uanataca: " . $e->getMessage());
            throw $e; 
        }
    }
}