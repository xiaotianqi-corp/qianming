<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function index(): JsonResponse
    {
        $certificates = Certificate::whereHas('orderItem.order', function($q) {
            $q->where('user_id', auth()->id());
        })->with('orderItem.product')->get();

        return response()->json($certificates);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        $this->authorizeOwner($certificate);
        return response()->json($certificate->load('orderItem.product'));
    }

    public function download(Certificate $certificate): StreamedResponse|JsonResponse
    {
        $this->authorizeOwner($certificate);

        if (!$certificate->path || !Storage::disk('s3')->exists($certificate->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        AuditEvent::log('certificate_downloaded', [
            'certificate_id' => $certificate->id,
            'serial' => $certificate->external_id
        ]);

        return Storage::disk('s3')->download($certificate->path, "certificate_{$certificate->external_id}.p12");
    }

    public function revoke(Certificate $certificate): JsonResponse
    {
        $this->authorizeOwner($certificate);

        if ($certificate->status === 'revoked') {
            return response()->json(['error' => 'The certificate has already been revoked'], 422);
        }

        AuditEvent::log('certificate_revocation_requested', [
            'certificate_id' => $certificate->id,
            'serial' => $certificate->external_id
        ]);

        // Aquí se dispararía el Job hacia Uanataca para revocar
        $certificate->update(['status' => 'revoked', 'revoked_at' => now()]);

        return response()->json(['message' => 'Revocation request successfully submitted.']);
    }

    private function authorizeOwner(Certificate $certificate)
    {
        if ($certificate->orderItem->order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}