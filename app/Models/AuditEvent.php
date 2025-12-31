<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    protected $fillable = [
        'event', 'user_id', 'actor_type', 'actor_id', 
        'context', 'ip', 'user_agent'
    ];

    protected $casts = [
        'context' => 'array'
    ];

    public static function log(string $event, array $context = [], $actor = null)
    {
        return self::create([
            'event'      => $event,
            'user_id'    => auth()->id(),
            'actor_type' => $actor ? get_class($actor) : 'system',
            'actor_id'   => $actor?->id,
            'context'    => $context,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}