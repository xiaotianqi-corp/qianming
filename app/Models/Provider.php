<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{

    protected $fillable = [
        'name',
        'identifier',
    ];
    
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'provider_country_configs')
                    ->withPivot('requirements', 'active');
    }
}
