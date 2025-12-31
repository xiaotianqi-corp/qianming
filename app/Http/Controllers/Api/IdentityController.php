<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IdentityResource;
use App\Models\Identity;
use App\Models\Country;
use App\Models\AuditEvent;
use App\Enums\IdentityStatus;
use App\Services\IdentityRequirementService;
use App\Http\Requests\StoreIdentityRequest;
use App\Exceptions\InvalidDocumentTypeException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IdentityController extends Controller
{
    public function index()
    {
        $identities = Identity::where('user_id', auth()->id())
            ->with(['country', 'documents'])
            ->get();

        return IdentityResource::collection($identities);
    }
    
    public function store(StoreIdentityRequest $request, IdentityRequirementService $service): JsonResponse
    {
        if (config('features.disable_identity_submission')) {
            return response()->json(['error' => 'Registration temporarily disabled.'], 503);
        }

        return DB::transaction(function () use ($request, $service) {
            $country = Country::findOrFail($request->country_id);

            $identity = Identity::create(array_merge(
                $request->validated(),
                [
                    'user_id' => auth()->id(),
                    'status'  => IdentityStatus::SUBMITTED
                ]
            ));

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $type => $file) {
                    $path = $file->store("identities/{$identity->id}", 's3');
                    $identity->documents()->create([
                        'type' => $type,
                        'path' => $path
                    ]);
                }
            }

            try {
                $service->validate($identity, $country);
            } catch (InvalidDocumentTypeException $e) {
                return response()->json(['error' => 'PSC validation error: ' . $e->getMessage()], 422);
            }

            AuditEvent::log('identity_submitted', [
                'identity_id' => $identity->id,
                'country' => $country->code
            ]);
            
            return response()->json([
                'message' => 'Identity registered and sent for review.',
                'data' => new IdentityResource($identity->load(['country', 'documents']))
            ], 201);
        });
    }
    
    public function show(Identity $identity): JsonResponse
    {
        if ($identity->user_id !== auth()->id()) abort(403);
        return response()->json(new IdentityResource($identity->load(['country', 'documents'])));
    }
    
    public function update(Request $request, Identity $identity): JsonResponse
    {
        if ($identity->user_id !== auth()->id()) abort(403);
        
        if ($identity->status === IdentityStatus::VERIFIED) {
            return response()->json(['error' => 'You cannot edit an identity that has already been verified.'], 422);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'sometimes|string|max:100',
            'email'      => 'sometimes|email',
            'phone'      => 'sometimes|string',
        ]);

        $identity->update($validated);
        
        AuditEvent::log('identity_updated', ['identity_id' => $identity->id]);

        return response()->json([
            'message' => 'Data updated correctly.',
            'data' => new IdentityResource($identity)
        ]);
    }

    public function destroy(Identity $identity): JsonResponse
    {
        if ($identity->user_id !== auth()->id()) abort(403);

        AuditEvent::log('identity_deleted', ['identity_id' => $identity->id]);

        foreach ($identity->documents as $doc) {
            Storage::disk('s3')->delete($doc->path);
        }

        $identity->delete();

        return response()->json(['message' => 'Identity successfully removed.']);
    }
}