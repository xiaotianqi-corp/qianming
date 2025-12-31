<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignaturePlanController extends Controller
{
    public function index()
    {
        return SignatureProduct::query()
            ->where('active', true)
            ->get([
                'id',
                'container',
                'validity_years',
                'subscriber_type',
                'pvp'
            ]);
    }
}