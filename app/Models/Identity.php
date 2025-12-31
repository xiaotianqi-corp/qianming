<?php

namespace App\Models;

use App\Enums\IdentityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Identity extends Model
{
    protected $fillable = [
        'user_id',
        'country_id',
        'type',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'birth_date',
        'email',
        'phone',
        'status'
    ];
    
    protected $casts = [
        'status' => IdentityStatus::class,
        'birth_date' => 'date',
    ];
    
    public function isReadyForSignature(): bool
    {
        return $this->status === IdentityStatus::VERIFIED;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(IdentityDocument::class);
    }
}