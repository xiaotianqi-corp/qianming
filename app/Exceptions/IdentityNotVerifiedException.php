<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class IdentityNotVerifiedException extends Exception
{
    
    public function report(): void
    {
        \App\Models\AuditEvent::log('issuance_failed_unverified_identity', [
            'error' => $this->getMessage(),
        ]);
    }
    
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'Identity not verified',
            'message' => 'The holder has not passed the KYC validation process and cannot issue certificates.',
            'code' => 'IDENTITY_UNVERIFIED'
        ], 422);
    }
}