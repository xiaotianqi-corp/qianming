<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityDocument extends Model
{
    protected $fillable = [
        'identity_id',
        'type',
        'path'
    ];

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }
}
