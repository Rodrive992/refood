<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MozosController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function cajaAbierta(int $localId): ?Caja
    {
        return Caja::query()
            ->where('id_local', $localId)
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();
    }

    /**
     * GET /admin/caja/mozos
     */
    public function index(Request $request)
    {
        $localId = $this->localId();

        $q = trim((string) $request->get('q', ''));

        $cajaAbierta = $this->cajaAbierta($localId);

        $mozos = User::query()
            ->where('id_local', $localId)
            ->where('role', 'mozo')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            // Activo primero: estado='activo' arriba
            ->orderByRaw("CASE WHEN estado='activo' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'estado', 'created_at']);

        return view('admin.caja.mozos.index', compact('mozos', 'q', 'cajaAbierta'));
    }

    /**
     * PATCH /admin/caja/mozos/{user}/estado
     * Toggle automático: activo <-> inactivo
     * (No requiere body)
     */
    public function setEstado(Request $request, User $user)
    {
        $localId = $this->localId();

        abort_unless((int) $user->id_local === $localId, 403, 'Usuario fuera de tu local.');
        abort_unless(($user->role ?? null) === 'mozo', 422, 'Solo se puede cambiar el estado de usuarios mozo.');

        return DB::transaction(function () use ($localId, $user) {

            // ✅ Regla: solo permitir activar/inactivar si hay caja abierta
            $caja = Caja::query()
                ->where('id_local', $localId)
                ->where('estado', 'abierta')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (!$caja) {
                return back()->with('error', 'No hay caja abierta. Abrí un turno para habilitar mozos.');
            }

            // Toggle
            $actual = (string) ($user->estado ?? 'activo');
            $nuevo  = ($actual === 'activo') ? 'inactivo' : 'activo';

            $user->estado = $nuevo;
            $user->save();

            return back()->with('success', 'Estado actualizado: ' . $user->name . ' → ' . $nuevo . '.');
        });
    }

    /**
     * PATCH /admin/caja/mozos/{user}/nombre
     * Body: name=...
     */
    public function updateNombre(Request $request, User $user)
    {
        $localId = $this->localId();

        abort_unless((int) $user->id_local === $localId, 403, 'Usuario fuera de tu local.');
        abort_unless(($user->role ?? null) === 'mozo', 422, 'Solo se puede editar el nombre de usuarios mozo.');

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        // Normalizar espacios
        $name = trim(preg_replace('/\s+/', ' ', (string) $data['name']));

        // Opcional: no permitir nombre vacío luego de limpiar
        if ($name === '') {
            return back()->withErrors(['name' => 'El nombre no puede quedar vacío.']);
        }

        $user->name = $name;
        $user->save();

        return back()->with('success', 'Nombre actualizado: ' . $user->name . '.');
    }
}