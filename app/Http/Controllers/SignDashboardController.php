<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Signs/Dashboard', [
            'certificates' => CertificateRequest::query()
                ->whereHas('orderItem.order', fn ($q) =>
                    $q->where('user_id', auth()->id())
                )
                ->get()
        ]);
    }
}
