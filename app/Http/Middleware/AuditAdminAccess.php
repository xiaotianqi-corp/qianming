<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/internal/*')) {
            \App\Models\AuditEvent::log('admin_access', [
                'url'    => $request->fullUrl(),
                'method' => $request->method(),
                'params' => $request->except(['password', 'password_confirmation']),
                'status' => $response->getStatusCode(),
            ], auth()->user());
        }

        return $response;
    }
}
