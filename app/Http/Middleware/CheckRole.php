<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja la solicitud entrante.
     * * @param Request $request
     * @param Closure $next
     * @param string ...$roles Roles permitidos (ej: 'admin', 'compliance')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'error' => 'No autenticado.',
                'code' => 'unauthenticated'
            ], 401);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'error' => 'No tienes permisos (Rol: ' . $request->user()->role . ' no autorizado).',
                'code' => 'unauthorized_role'
            ], 403);
        }

        return $next($request);
    }
}