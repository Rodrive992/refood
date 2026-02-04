<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MesaController extends Controller
{
    /**
     * Panel / listado de mesas
     * GET /admin/mesas
     */
    public function index(Request $request)
    {
        // Por ahora: si no mandan id_local, usamos 1 (La Piscala)
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
        $idLocal = $mesa->id_local;

        $mesa->delete();

        return redirect()
            ->route('admin.mesas.index', ['id_local' => $idLocal])
            ->with('success', 'Mesa eliminada.');
    }

    /**
     * Cambio rápido de estado (para clicks en el panel)
     * PATCH /admin/mesas/{mesa}/estado
     */
    public function setEstado(Request $request, Mesa $mesa)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(Mesa::estados())],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        // Si la mesa vuelve a libre, limpiamos observación (útil)
        if ($data['estado'] === Mesa::ESTADO_LIBRE) {
            $data['observacion'] = null;
        }

        $mesa->update($data);

        return back()->with('success', 'Estado actualizado.');
    }

    /**
     * Liberar rápido: deja en libre y limpia observación
     * PATCH /admin/mesas/{mesa}/liberar
     */
    public function liberar(Mesa $mesa)
    {
        $mesa->update([
            'estado' => Mesa::ESTADO_LIBRE,
            'observacion' => null,
        ]);

        return back()->with('success', 'Mesa liberada.');
    }
}
