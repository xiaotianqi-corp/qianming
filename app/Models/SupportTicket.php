<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use App\Enums\TicketSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
use Illuminate\Database\Eloquent\Casts\Attribute;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'certificate_request_id',
        'subject',
        'description',
        'category',
        'status',
        'priority',
        'source',
        'urgency',
        'assigned_to',
        'group_id',
        'location_id',
        'sla_policy_id',
        'provider_payload',
        'first_responded_at',
        'resolved_at',
        'due_by',
        'fr_due_by',
        'is_escalated',
        'fr_escalated',
        'cc_emails',
        'tags',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'category' => TicketCategory::class,
        'source' => TicketSource::class,
        'provider_payload' => 'array',
        'cc_emails' => 'array',
        'tags' => 'array',
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'due_by' => 'datetime',
        'fr_due_by' => 'datetime',
        'is_escalated' => 'boolean',
        'fr_escalated' => 'boolean',
    ];

    protected $appends = [
        'is_overdue',
        'response_time',
        'status_label',
        'priority_label',
        'category_label',
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }

            // Asignar SLA por defecto si no se especifica
            if (!$ticket->sla_policy_id) {
                $defaultSla = SlaPolicy::default()->first();
                if ($defaultSla) {
                    $ticket->sla_policy_id = $defaultSla->id;
                    $ticket->calculateDueDates($defaultSla);
                }
            }
        });
    }

    // Relaciones adicionales
    public function group(): BelongsTo
    {
        return $this->belongsTo(AgentGroup::class, 'group_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    // Método para calcular due dates basado en SLA
    public function calculateDueDates(?SlaPolicy $sla = null): void
    {
        $sla = $sla ?? $this->slaPolicy;
        
        if (!$sla) return;

        $businessHours = $sla->businessHours;
        
        if ($businessHours) {
            // Calcular con horario laboral
            $this->fr_due_by = $this->calculateBusinessHoursDue(
                $this->created_at,
                $sla->first_response_time,
                $businessHours
            );
            
            $this->due_by = $this->calculateBusinessHoursDue(
                $this->created_at,
                $sla->resolution_time,
                $businessHours
            );
        } else {
            // Calcular con horas corridas
            $this->fr_due_by = $this->created_at->addMinutes($sla->first_response_time);
            $this->due_by = $this->created_at->addMinutes($sla->resolution_time);
        }
    }

    private function calculateBusinessHoursDue($startTime, $minutes, $businessHours)
    {
        // Implementación simplificada - puedes mejorar esto
        return $startTime->addMinutes($minutes);
    }

    // Accessor actualizado
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->isOpen() 
                && ($this->due_by?->isPast() ?? false)
        );
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(TicketConversation::class, 'ticket_id');
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $number = "{$prefix}-{$date}-{$random}";
        
        while (self::where('ticket_number', $number)->exists()) {
            $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $number = "{$prefix}-{$date}-{$random}";
        }
        
        return $number;
    }
}