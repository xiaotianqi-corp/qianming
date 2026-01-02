<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateRequest;
use App\Models\WebhookEvent;
use App\Helpers\Audit;
use App\Jobs\DownloadCertificateJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UanatacaWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->validateSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventId = $request->input('event_id');
        
        if (WebhookEvent::where('external_id', $eventId)->exists()) {
            return response()->json(['ok' => true, 'message' => 'Already processed'], 200);
        }

        WebhookEvent::create([
            'external_id' => $eventId,
            'event_type' => $request->input('event'),
            'payload' => $request->all(),
            'processed_at' => now(),
        ]);

        $event = $request->input('event');
        
        match ($event) {
            'certificate.issued' => $this->handleIssued($request),
            'certificate.revoked' => $this->handleRevoked($request),
            'certificate.failed' => $this->handleFailed($request),
            default => Log::info("Unhandled Uanataca event: {$event}")
        };

        return response()->json(['ok' => true]);
    }

    protected function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Uanataca-Signature');
        $secret = config('services.uanataca.webhook_secret');
        
        if (!$signature || !$secret) {
            return false;
        }
        
        $computed = hash_hmac('sha256', json_encode($request->all()), $secret);
        return hash_equals($computed, $signature);
    }

    protected function handleIssued(Request $request): void
    {
        $externalId = $request->input('external_id');
        $certRequest = CertificateRequest::where('external_id', $externalId)->first();
        
        if (!$certRequest) {
            Log::warning("Certificate request not found: {$externalId}");
            return;
        }

        $certRequest->update(['status' => 'issued']);
        
        Audit::log('certificate_issued_webhook', [
            'certificate_request_id' => $certRequest->id,
            'external_id' => $externalId,
        ]);

        DownloadCertificateJob::dispatch($certRequest, $request->all());
    }

    protected function handleRevoked(Request $request): void
    {
        $externalId = $request->input('external_id');
        $certRequest = CertificateRequest::where('external_id', $externalId)->first();
        
        if ($certRequest) {
            $certRequest->certificate?->update([
                'status' => 'revoked',
                'revoked_at' => now(),
            ]);
            
            Audit::log('certificate_revoked_webhook', [
                'external_id' => $externalId,
                'reason' => $request->input('reason'),
            ]);
        }
    }

    protected function handleFailed(Request $request): void
    {
        $externalId = $request->input('external_id');
        $certRequest = CertificateRequest::where('external_id', $externalId)->first();
        
        if ($certRequest) {
            $certRequest->update([
                'status' => 'failed',
                'error_log' => $request->input('error_message'),
            ]);
            
            Audit::log('certificate_failed_webhook', [
                'external_id' => $externalId,
                'error' => $request->input('error_message'),
            ]);
        }
    }
}