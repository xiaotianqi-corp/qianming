<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'certificate_request_id',
        'category',
        'status',
        'priority',
        'description',
        'provider_payload'
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'provider_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificateRequest(): BelongsTo
    {
        return $this->belongsTo(CertificateRequest::class);
    }
}