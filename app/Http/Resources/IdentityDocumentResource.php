<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IdentityDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'url' => $this->path ? Storage::disk('s3')->temporaryUrl($this->path, now()->addMinutes(10)) : null,
            'compliance_view_url' => route('internal.compliance.documents.view', $this->id),
        ];
    }
}