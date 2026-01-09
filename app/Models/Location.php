<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'phone',
        'email',
        'contact_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
