<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comanda;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MesaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_unless((int)$mesa->id_local === $this->localId(), 403, 'Mesa fuera de tu local.');
    }

    /**
     * Panel / listado de mesas
     * GET /admin/mesas
     */
    public function index(Request $request)
    {
        // Por ahora: si no mandan id_local, usamos 1 (La Piscala)
        // (si querés, después lo hacemos multi-local real como en otros controllers)
        $idLocal = (int) $request->get('id_local', 1);

        $estado = $request->get('estado'); // opcional para filtrar

        $query = Mesa::query()->where('id_local', $idLocal);

        if ($estado) {
            $query->where('estado', $estado);
        }

        $mesas = $query
            ->orderByRaw("CAST(REGEXP_REPLACE(nombre, '[^0-9]', '') AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get();

        $estados = Mesa::estados();

        return view('admin.mesas.index', compact('mesas', 'idLocal', 'estado', 'estados'));
    }

    /**
     * Crear mesa
     * POST /admin/mesas
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_local' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
            'estado' => ['required', Rule::in(Mesa::estados())],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        Mesa::create($data);

        return redirect()
            ->route('admin.mesas.index', ['id_local' => $data['id_local']])
            ->with('success', 'Mesa creada correctamente.');
    }

    /**
     * Actualizar mesa
     * PUT /admin/mesas/{mesa}
     */
    public function update(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
            'estado' => ['required', Rule::in(Mesa::estados())],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $mesa->update($data);

        return back()->with('success', 'Mesa actualizada.');
    }

    /**
     * Eliminar mesa
     * DELETE /admin/mesas/{mesa}
     */
    public function destroy(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $idLocal = $mesa->id_local;

        $mesa->delete();

        return redirect()
            ->route('admin.mesas.index', ['id_local' => $idLocal])
            ->with('success', 'Mesa eliminada.');
    }

    /**
     * Cambio rápido de estado
     * PATCH /admin/mesas/{mesa}/estado
     */
    public function setEstado(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $data = $request->validate([
            'estado' => ['required', Rule::in(Mesa::estados())],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        // ✅ Si la mesa vuelve a libre, además anulamos comandas activas y limpiamos obs
        if ($data['estado'] === Mesa::ESTADO_LIBRE) {
            DB::transaction(function () use ($mesa) {
                $this->anularComandasActivasDeMesa($mesa);

                $mesa->update([
                    'estado' => Mesa::ESTADO_LIBRE,
                    'observacion' => null,
                ]);
            });

            return back()->with('success', 'Mesa liberada (comandas activas anuladas).');
        }

        $mesa->update($data);

        return back()->with('success', 'Estado actualizado.');
    }

    /**
     * Liberar rápido
     * PATCH /admin/mesas/{mesa}/liberar
     */
    public function liberar(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        DB::transaction(function () use ($mesa) {

            // ✅ 1) anular comandas activas de esta mesa
            $this->anularComandasActivasDeMesa($mesa);

            // ✅ 2) liberar mesa
            $mesa->update([
                'estado' => Mesa::ESTADO_LIBRE,
                'observacion' => null,
            ]);
        });

        return back()->with('success', 'Mesa liberada (comandas activas anuladas).');
    }

    /**
     * Anula comandas activas de una mesa
     */
    private function anularComandasActivasDeMesa(Mesa $mesa): void
    {
        $estadosActivos = ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'];

        Comanda::query()
            ->where('id_local', (int)$mesa->id_local)
            ->where('id_mesa', (int)$mesa->id)
            ->whereIn('estado', $estadosActivos)
            ->update([
                'estado' => 'anulada',
            ]);
    }
}
