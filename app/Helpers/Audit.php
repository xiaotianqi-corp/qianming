<?php

namespace App\Helpers;

use App\Models\AuditEvent;

class Audit
{
    public static function log(string $event, array $context = []): AuditEvent
    {
        return AuditEvent::create([
            'event' => $event,
            'user_id' => auth()->id(),
            'actor_type' => auth()->check() ? 'user' : 'system',
            'actor_id' => auth()->id(),
            'context' => $context,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}