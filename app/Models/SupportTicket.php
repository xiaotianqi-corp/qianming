<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'certificate_request_id',
        'subject',
        'category', 
        'status',
        'priority',
        'source',
        'urgency',
        'impact', 
        'group',
        'agent',
        'description',
        'provider_payload'
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'provider_payload' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $prefix = 'TKT-' . now()->format('ymd'); 
                
                $randomPart = strtoupper(substr(md5(uniqid()), 0, 4));
                
                $ticket->ticket_number = "{$prefix}-{$randomPart}";
                
                while (self::where('ticket_number', $ticket->ticket_number)->exists()) {
                    $randomPart = strtoupper(substr(md5(uniqid()), 0, 4));
                    $ticket->ticket_number = "{$prefix}-{$randomPart}";
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificateRequest(): BelongsTo
    {
        return $this->belongsTo(CertificateRequest::class);
    }
}