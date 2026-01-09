<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

class AgentGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'escalate_to',
        'unassigned_for',
        'active',
    ];

    protected $casts = [
        'escalate_to' => 'boolean',
        'active' => 'boolean',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agent_group_members')
            ->withPivot('is_observer')
            ->withTimestamps();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}