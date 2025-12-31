<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IdentityDocument;
use App\Models\Identity;
use App\Models\AuditEvent;
use App\Http\Resources\IdentityDocumentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class IdentityDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['identity_id' => 'required|exists:identities,id']);
        
        $identity = Identity::findOrFail($request->identity_id);
        if ($identity->user_id !== auth()->id()) abort(403);

        $documents = $identity->documents;
        return response()->json(IdentityDocumentResource::collection($documents));
    }
    
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'identity_id' => 'required|exists:identities,id',
            'type'        => 'required|string|in:front_id,back_id,selfie,additional',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $identity = Identity::findOrFail($request->identity_id);
        if ($identity->user_id !== auth()->id()) abort(403);

        $path = $request->file('file')->store("identities/{$identity->id}", 's3');

        $document = $identity->documents()->create([
            'type' => $request->type,
            'path' => $path,
        ]);

        AuditEvent::log('identity_document_uploaded', ['document_id' => $document->id]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data'    => new IdentityDocumentResource($document)
        ], 201);
    }
    
    public function show(IdentityDocument $identityDocument): JsonResponse
    {
        if ($identityDocument->identity->user_id !== auth()->id()) abort(403);

        return response()->json(new IdentityDocumentResource($identityDocument));
    }
    
    public function update(Request $request, IdentityDocument $identityDocument): JsonResponse
    {
        if ($identityDocument->identity->user_id !== auth()->id()) abort(403);

        $request->validate([
            'type' => 'sometimes|string',
            'file' => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('s3')->delete($identityDocument->path);
            $identityDocument->path = $request->file('file')->store("identities/{$identityDocument->identity_id}", 's3');
        }

        if ($request->type) $identityDocument->type = $request->type;
        
        $identityDocument->save();

        return response()->json(['message' => 'Updated document', 'data' => new IdentityDocumentResource($identityDocument)]);
    }
    
    public function destroy(IdentityDocument $identityDocument): JsonResponse
    {
        if ($identityDocument->identity->user_id !== auth()->id()) abort(403);

        Storage::disk('s3')->delete($identityDocument->path);
        $identityDocument->delete();

        AuditEvent::log('identity_document_deleted', ['identity_id' => $identityDocument->identity_id]);

        return response()->json(['message' => 'Document deleted']);
    }
}