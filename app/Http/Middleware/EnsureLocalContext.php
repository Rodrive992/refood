<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureLocalContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Si el usuario no tiene local asignado, no puede usar el sistema
        if (empty($user->id_local)) {
            abort(403, 'Tu usuario no tiene un local asignado. Contactá al administrador.');
        }

        // Guardamos el local actual para usarlo en toda la app
        app()->instance('local_id', (int) $user->id_local);

        return $next($request);
    }
}
