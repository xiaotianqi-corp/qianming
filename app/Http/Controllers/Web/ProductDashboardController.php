<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => auth()->user()->orders()
                ->with('items.signatureProduct')
                ->get()
        ]);
    }
}
