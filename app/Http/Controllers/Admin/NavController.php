<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comanda;
use App\Models\Mesa;
use Illuminate\Http\Request;

class NavController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    public function poll(Request $request)
    {
        $localId = $this->localId();

        $estadosActivos = ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'];

        $cajaPendientes = (int) Comanda::query()
            ->where('id_local', $localId)
            ->where('cuenta_solicitada', 1)
            ->whereIn('estado', $estadosActivos)
            ->count();

        $comandasActivas = (int) Comanda::query()
            ->where('id_local', $localId)
            ->whereIn('estado', $estadosActivos)
            ->count();

        $mesasOcupadas = (int) Mesa::query()
            ->where('id_local', $localId)
            ->where('estado', 'ocupada')
            ->count();

        return response()->json([
            'ok' => true,
            'caja_pendientes' => $cajaPendientes,
            'comandas_activas' => $comandasActivas,
            'mesas_ocupadas' => $mesasOcupadas,
            'ts' => now()->toISOString(),
        ]);
    }
}