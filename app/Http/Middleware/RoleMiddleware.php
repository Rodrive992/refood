<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Maneja el acceso por roles.
     * Uso: ->middleware('role:admin') o ->middleware('role:mozo,admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // No autenticado
        if (!$user) {
            return redirect()->route('login');
        }

        // Usuario sin rol asignado
        if (empty($user->role)) {
            abort(403, 'Usuario sin rol asignado.');
        }

        // Rol no permitido
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'No tenés permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
