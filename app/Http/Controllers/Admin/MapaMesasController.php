<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalMapa;
use App\Models\LocalMapaCelda;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapaMesasController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function getOrCreateMapa(int $idLocal): LocalMapa
    {
        return LocalMapa::firstOrCreate(
            ['id_local' => $idLocal],
            [
                'filas' => 10,
                'columnas' => 12,
                'caja_x' => 1,
                'caja_y' => 1,
            ]
        );
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_unless((int) $mesa->id_local === $this->localId(), 403, 'Mesa fuera de tu local.');
    }

    /**
     * GET /admin/mesas/mapa
     */
    public function index(Request $request)
    {
        $idLocal = $this->localId();

        $mapa = $this->getOrCreateMapa($idLocal);

        $celdas = LocalMapaCelda::query()
            ->where('id_local', $idLocal)
            ->get();

        $mesas = Mesa::query()
            ->where('id_local', $idLocal)
            ->orderByRaw("CAST(REGEXP_REPLACE(nombre, '[^0-9]', '') AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get();

        $paredes = $celdas
            ->where('tipo', LocalMapaCelda::TIPO_PARED)
            ->mapWithKeys(fn ($c) => [$c->x . '-' . $c->y => true]);

        return view('admin.mesas.mapa', [
            'idLocal'  => $idLocal,
            'mapa'     => $mapa,
            'mesas'    => $mesas,
            'celdas'   => $celdas,
            'paredes'  => $paredes,
        ]);
    }

    /**
     * PATCH /admin/mesas/mapa/config
     */
    public function updateConfig(Request $request)
    {
        $idLocal = $this->localId();

        $data = $request->validate([
            'filas'    => ['required', 'integer', 'min:3', 'max:50'],
            'columnas' => ['required', 'integer', 'min:3', 'max:50'],
        ]);

        $mapa = $this->getOrCreateMapa($idLocal);

        DB::transaction(function () use ($mapa, $data, $idLocal) {
            $mapa->update([
                'filas'    => $data['filas'],
                'columnas' => $data['columnas'],
            ]);

            // Limpiar paredes fuera del nuevo rango
            LocalMapaCelda::query()
                ->where('id_local', $idLocal)
                ->where(function ($q) use ($data) {
                    $q->where('x', '>', $data['columnas'])
                      ->orWhere('y', '>', $data['filas']);
                })
                ->delete();

            // Quitar mesas fuera del nuevo rango
            Mesa::query()
                ->where('id_local', $idLocal)
                ->where(function ($q) use ($data) {
                    $q->where('pos_x', '>', $data['columnas'])
                      ->orWhere('pos_y', '>', $data['filas']);
                })
                ->update([
                    'pos_x' => null,
                    'pos_y' => null,
                ]);
        });

        return back()->with('success', 'Configuración del mapa actualizada.');
    }

    /**
     * PATCH /admin/mesas/mapa/caja
     */
    public function updateCaja(Request $request)
    {
        $idLocal = $this->localId();
        $mapa = $this->getOrCreateMapa($idLocal);

        $data = $request->validate([
            'caja_x' => ['required', 'integer', 'min:1'],
            'caja_y' => ['required', 'integer', 'min:1'],
        ]);

        abort_if(
            $data['caja_x'] > $mapa->columnas || $data['caja_y'] > $mapa->filas,
            422,
            'La posición de la caja está fuera del mapa.'
        );

        $hayPared = LocalMapaCelda::query()
            ->where('id_local', $idLocal)
            ->where('x', $data['caja_x'])
            ->where('y', $data['caja_y'])
            ->where('tipo', LocalMapaCelda::TIPO_PARED)
            ->exists();

        abort_if($hayPared, 422, 'No podés ubicar la caja sobre una pared.');

        $mesaEnCelda = Mesa::query()
            ->where('id_local', $idLocal)
            ->where('pos_x', $data['caja_x'])
            ->where('pos_y', $data['caja_y'])
            ->exists();

        abort_if($mesaEnCelda, 422, 'No podés ubicar la caja sobre una mesa.');

        $mapa->update([
            'caja_x' => $data['caja_x'],
            'caja_y' => $data['caja_y'],
        ]);

        return back()->with('success', 'Caja reubicada correctamente.');
    }

    /**
     * POST /admin/mesas/mapa/celdas/toggle
     */
    public function toggleCelda(Request $request)
    {
        $idLocal = $this->localId();
        $mapa = $this->getOrCreateMapa($idLocal);

        $data = $request->validate([
            'x' => ['required', 'integer', 'min:1'],
            'y' => ['required', 'integer', 'min:1'],
        ]);

        abort_if(
            $data['x'] > $mapa->columnas || $data['y'] > $mapa->filas,
            422,
            'La celda está fuera del mapa.'
        );

        // No permitir pared sobre caja
        if ((int) $mapa->caja_x === (int) $data['x'] && (int) $mapa->caja_y === (int) $data['y']) {
            return back()->withErrors([
                'mapa' => 'No podés marcar como pared la celda de la caja.',
            ]);
        }

        // No permitir pared sobre mesa
        $mesaEnCelda = Mesa::query()
            ->where('id_local', $idLocal)
            ->where('pos_x', $data['x'])
            ->where('pos_y', $data['y'])
            ->exists();

        if ($mesaEnCelda) {
            return back()->withErrors([
                'mapa' => 'No podés marcar como pared una celda ocupada por una mesa.',
            ]);
        }

        $celda = LocalMapaCelda::query()
            ->where('id_local', $idLocal)
            ->where('x', $data['x'])
            ->where('y', $data['y'])
            ->first();

        if ($celda) {
            $celda->delete();
            return back()->with('success', 'Celda liberada.');
        }

        LocalMapaCelda::create([
            'id_local' => $idLocal,
            'x'        => $data['x'],
            'y'        => $data['y'],
            'tipo'     => LocalMapaCelda::TIPO_PARED,
        ]);

        return back()->with('success', 'Pared agregada.');
    }

    /**
     * PATCH /admin/mesas/mapa/mesas/{mesa}/posicion
     */
    public function updateMesaPosicion(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $idLocal = $this->localId();
        $mapa = $this->getOrCreateMapa($idLocal);

        $data = $request->validate([
            'pos_x' => ['required', 'integer', 'min:1'],
            'pos_y' => ['required', 'integer', 'min:1'],
        ]);

        abort_if(
            $data['pos_x'] > $mapa->columnas || $data['pos_y'] > $mapa->filas,
            422,
            'La posición está fuera del mapa.'
        );

        // Validar pared
        $hayPared = LocalMapaCelda::query()
            ->where('id_local', $idLocal)
            ->where('x', $data['pos_x'])
            ->where('y', $data['pos_y'])
            ->where('tipo', LocalMapaCelda::TIPO_PARED)
            ->exists();

        abort_if($hayPared, 422, 'No podés ubicar una mesa sobre una pared.');

        // Validar caja
        abort_if(
            (int) $mapa->caja_x === (int) $data['pos_x'] && (int) $mapa->caja_y === (int) $data['pos_y'],
            422,
            'No podés ubicar una mesa sobre la caja.'
        );

        // Validar otra mesa
        $hayOtraMesa = Mesa::query()
            ->where('id_local', $idLocal)
            ->where('id', '!=', $mesa->id)
            ->where('pos_x', $data['pos_x'])
            ->where('pos_y', $data['pos_y'])
            ->exists();

        abort_if($hayOtraMesa, 422, 'Ya existe otra mesa en esa posición.');

        $mesa->update([
            'pos_x' => $data['pos_x'],
            'pos_y' => $data['pos_y'],
        ]);

        return back()->with('success', 'Mesa ubicada correctamente.');
    }

    /**
     * PATCH /admin/mesas/mapa/mesas/{mesa}/quitar
     */
    public function quitarMesa(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $mesa->update([
            'pos_x' => null,
            'pos_y' => null,
        ]);

        return back()->with('success', 'Mesa quitada del mapa.');
    }

    /**
     * DELETE /admin/mesas/mapa/paredes
     */
    public function limpiarParedes()
    {
        $idLocal = $this->localId();

        LocalMapaCelda::query()
            ->where('id_local', $idLocal)
            ->where('tipo', LocalMapaCelda::TIPO_PARED)
            ->delete();

        return back()->with('success', 'Se limpiaron todas las paredes del mapa.');
    }
}