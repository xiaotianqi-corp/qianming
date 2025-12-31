<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'type',
        'identification',
        'name',
        'email',
        'phone'
    ];
}
