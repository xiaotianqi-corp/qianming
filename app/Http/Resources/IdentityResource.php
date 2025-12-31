<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdentityResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => "{$this->first_name} {$this->last_name}",
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'country' => $this->country->name ?? 'N/A',
            'country_code' => $this->country->code ?? 'N/A',
            'status' => [
                'id'    => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'can_edit'   => $this->status->value === 'draft' || $this->status->value === 'rejected',
            'documents' => IdentityDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}