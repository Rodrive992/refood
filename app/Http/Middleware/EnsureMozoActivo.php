<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMozoActivo
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Si no está logueado, auth lo maneja.
        if (!$user) {
            return $next($request);
        }

        // Admin siempre pasa
        if (($user->role ?? null) === 'admin') {
            return $next($request);
        }

        // Mozo inactivo: afuera
        if (($user->role ?? null) === 'mozo') {
            $estado = (string) ($user->estado ?? 'activo');

            if ($estado !== 'activo') {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Tu usuario está inactivo. Pedí al administrador que te habilite.']);
            }
        }

        return $next($request);
    }
}