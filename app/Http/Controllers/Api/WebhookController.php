<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Models\AuditEvent;
use App\Jobs\DownloadCertificateJob;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function uanataca(Request $request)
    {
        $allowedIps = config('services.uanataca.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            return response()->json(['error' => 'Unauthorized IP'], 403);
        }

        $signature = $request->header('X-Uanataca-Signature');
        if (!$this->isValidSignature($request->all(), $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventId = $request->input('event_id');
        if ($eventId && AuditEvent::where('context->event_id', $eventId)->exists()) {
            return response()->json(['message' => 'Event already processed'], 200);
        }

        $certRequest = CertificateRequest::where('external_id', $request->input('id'))->first();

        if (!$certRequest) {
            Log::warning("Uanataca Webhook: Solicitud no encontrada: " . $request->input('id'));
            return response()->json(['error' => 'Request not found'], 404);
        }

        $status = $request->input('status');
        $this->processStatus($certRequest, $status, $request->all());

        return response()->json(['status' => 'success'], 200);
    }

    protected function isValidSignature($payload, $signature): bool
    {
        $secret = config('services.uanataca.webhook_secret');
        if (!$signature || !$secret) return false;
        
        $computedSignature = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $secret);
        return hash_equals($computedSignature, $signature);
    }

    protected function processStatus(CertificateRequest $certRequest, string $status, array $payload)
    {
        $oldStatus = $certRequest->status;
        $certRequest->update(['status' => $status]);

        AuditEvent::log('psc_status_updated', [
            'certificate_request_id' => $certRequest->id,
            'event_id' => $payload['event_id'] ?? null,
            'from' => $oldStatus,
            'to' => $status,
            'external_id' => $certRequest->external_id
        ]);

        if ($status === 'issued') {
            DownloadCertificateJob::dispatch($certRequest, $payload);
        }
    }
}