<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Verificar si el usuario está autenticado
        if (! auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $user = auth()->user();

        // Verificar si el usuario tiene el permiso requerido
        if (! $user->hasPermission($permission)) {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        return $next($request);
    }
}
