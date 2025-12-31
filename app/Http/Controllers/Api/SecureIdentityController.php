<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Identity;
use App\Models\IdentityDocument;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class SecureIdentityController extends Controller
{

    public function index(): JsonResponse
    {
        $identities = Identity::with(['country', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($identities);
    }

    public function show(Identity $identity): JsonResponse
    {
        AuditEvent::log('sensitive_data_accessed', [
            'identity_id' => $identity->id,
            'owner_email' => $identity->email,
            'reason' => 'Compliance verification'
        ], $identity);

        return response()->json($identity->load('documents'));
    }

    public function viewDocument(IdentityDocument $document): JsonResponse
    {
        AuditEvent::log('identity_document_viewed', [
            'document_id' => $document->id,
            'file_type' => $document->type,
            'identity_id' => $document->identity_id
        ]);

        $url = Storage::disk('s3')->temporaryUrl(
            $document->path,
            now()->addMinutes(5)
        );

        return response()->json([
            'url' => $url,
            'expires_at' => now()->addMinutes(5)->toIso8601String(),
            'disclaimer' => 'This access has been audited under GDPR/LATAM standards.'
        ]);
    }

    public function verify(Request $request, Identity $identity): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'required|string|min:10'
        ]);

        $identity->update([
            'verified' => $validated['status'] === 'verified',
        ]);

        AuditEvent::log('identity_verification_changed', [
            'new_status' => $validated['status'],
            'notes' => $validated['notes']
        ], $identity);

        return response()->json(['message' => 'Updated identity status.']);
    }

    public function destroy(Identity $identity): JsonResponse
    {
        foreach ($identity->documents as $doc) {
            Storage::disk('s3')->delete($doc->path);
        }

        $identity->delete();

        AuditEvent::log('identity_permanently_deleted', [
            'identity_id' => $identity->id,
            'context' => 'GDPR Right to be forgotten'
        ]);
        
        AuditEvent::log('identity_document_viewed', [
            'document_id' => $document->id,
            'owner_id' => $document->identity->user_id,
            'reason' => 'Compliance Review'
        ], auth()->user());

        return response()->json(['message' => 'Identity and documents permanently deleted.']);
    }
}