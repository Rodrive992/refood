<?php

namespace App\Http\Middleware;

use App\Models\Caja;
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

        // Solo aplicamos reglas a mozo
        if (($user->role ?? null) === 'mozo') {

            // 1) Mozo inactivo: afuera
            $estado = (string) ($user->estado ?? 'activo');
            if ($estado !== 'activo') {
                return $this->kick($request, 'Tu usuario está inactivo. Pedí al administrador que te habilite.');
            }

            // 2) Sin local asignado: afuera (por seguridad)
            $localId = (int) ($user->id_local ?? 0);
            if ($localId <= 0) {
                return $this->kick($request, 'Tu usuario no tiene un local asignado.');
            }

            // 3) Caja/turno cerrado (no hay caja abierta): afuera
            $hayCajaAbierta = Caja::query()
                ->where('id_local', $localId)
                ->where('estado', 'abierta')
                ->exists();

            if (!$hayCajaAbierta) {
                return $this->kick($request, 'No hay un turno de caja abierto. Esperá a que el administrador abra caja.');
            }
        }

        return $next($request);
    }

    private function kick(Request $request, string $msg)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['email' => $msg]);
    }
}