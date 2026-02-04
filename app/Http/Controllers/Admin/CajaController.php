<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    public function index(Request $request)
    {
        $localId = $this->localId();

        $mesas = Mesa::query()
            ->where('id_local', $localId)
            ->whereNotIn('estado', ['fuera_servicio', 'inactiva'])
            ->orderByRaw("CAST(REGEXP_REPLACE(nombre, '[^0-9]', '') AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'estado', 'observacion', 'atendida_por', 'atendida_at']);

        $comandasPendientes = Comanda::query()
            ->where('id_local', $localId)
            ->where('cuenta_solicitada', 1)
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'])
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('cuenta_solicitada_at')
            ->get();

        // ✅ CLAVE para el aside de mesas (por mesa_id)
        $pendientesPorMesa = $comandasPendientes->keyBy('id_mesa');

        return view('admin.caja.index', compact('mesas', 'comandasPendientes', 'pendientesPorMesa'));
    }

    public function show(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'Esa comanda todavía NO solicitó la cuenta. Caja solo cobra cuando el mozo la solicita.');
        }

        $comanda->load([
            'mesa',
            'mozo',
            'items' => fn($q) => $q->orderBy('id'),
        ]);

        $subtotal = (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);

        return view('admin.caja.show', compact('comanda', 'subtotal'));
    }

    public function cuenta(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'La cuenta todavía no fue solicitada para esa comanda.');
        }

        $comanda->load(['mesa', 'mozo', 'items' => fn($q) => $q->orderBy('id')]);

        $subtotal = (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);

        return view('admin.caja.cuenta', compact('comanda', 'subtotal'));
    }

    public function cobrar(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'No se puede cobrar: la cuenta no fue solicitada.');
        }

        if (in_array($comanda->estado, ['cerrada', 'anulada'], true)) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'La comanda ya está cerrada/anulada.');
        }

        $data = $request->validate([
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'recargo'   => ['nullable', 'numeric', 'min:0'],
            'nota'      => ['nullable', 'string', 'max:255'],

            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.tipo' => ['required', 'in:efectivo,debito,transferencia'],
            'pagos.*.monto' => ['nullable', 'numeric', 'min:0'], // ✅ lo validamos como >=0
            'pagos.*.referencia' => ['nullable', 'string', 'max:120'],
        ]);

        // ✅ Filtramos pagos en 0 para evitar errores y registros basura
        $pagos = collect($data['pagos'] ?? [])
            ->filter(function ($p) {
                $m = (float)($p['monto'] ?? 0);
                return $m > 0;
            })
            ->values()
            ->all();

        if (count($pagos) === 0) {
            return back()
                ->withErrors(['pagos' => 'Ingresá al menos un pago con monto mayor a 0.'])
                ->withInput();
        }

        return DB::transaction(function () use ($comanda, $data, $pagos, $localId) {

            $subtotal = (float) $comanda->items()
                ->where('estado', '!=', 'anulado')
                ->sum(DB::raw('precio_snapshot * cantidad'));

            $descuento = (float) ($data['descuento'] ?? 0);
            $recargo   = (float) ($data['recargo'] ?? 0);

            $total = max(0, $subtotal - $descuento + $recargo);

            $pagadoTotal = 0.0;
            foreach ($pagos as $p) {
                $pagadoTotal += (float) ($p['monto'] ?? 0);
            }

            if ($pagadoTotal + 0.00001 < $total) {
                return back()
                    ->withErrors(['pagos' => 'El total pagado es menor al total a cobrar.'])
                    ->withInput();
            }

            $vuelto = max(0, $pagadoTotal - $total);

            $ya = Venta::query()->where('id_comanda', $comanda->id)->first();
            if ($ya) {
                return redirect()
                    ->route('admin.caja.index')
                    ->with('error', 'Esta comanda ya fue cobrada.');
            }

            $venta = Venta::create([
                'id_local'     => $localId,
                'id_comanda'   => $comanda->id,
                'id_mesa'      => $comanda->id_mesa,
                'id_mozo'      => $comanda->id_mozo,
                'estado'       => 'pagada',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'recargo'      => $recargo,
                'total'        => $total,
                'pagado_total' => $pagadoTotal,
                'vuelto'       => $vuelto,
                'nota'         => $data['nota'] ?? null,
                'sold_at'      => now(),
            ]);

            foreach ($pagos as $p) {
                Pago::create([
                    'id_venta'    => $venta->id,
                    'tipo'        => $p['tipo'],
                    'monto'       => (float) $p['monto'],
                    'referencia'  => $p['referencia'] ?? null,
                    'recibido_at' => now(),
                ]);
            }

            $comanda->update([
                'estado'      => 'cerrada',
                'closed_at'   => now(),
                'estado_caja' => 'cobrada',
            ]);

            if ($comanda->id_mesa) {
                Mesa::query()
                    ->where('id', $comanda->id_mesa)
                    ->where('id_local', $localId)
                    ->update([
                        'estado'       => 'libre',
                        'observacion'  => null,
                        'atendida_por' => null,
                        'atendida_at'  => null,
                    ]);
            }

            // ✅ Si tu ticket redirige a caja luego de imprimir, está OK pasar "back"
            return redirect()
                ->route('admin.ventas.ticket', [
                    'venta' => $venta->id,
                    'back'  => route('admin.caja.index'),
                ])
                ->with('ok', 'Venta registrada. Ticket listo para imprimir.');
        });
    }

    public function pendientes(Request $request)
    {
        $localId = $this->localId();

        $comandasPendientes = Comanda::query()
            ->where('id_local', $localId)
            ->where('cuenta_solicitada', 1)
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada', 'cerrando'])
            ->with(['mesa', 'mozo'])
            ->withCount(['items as items_count'])
            ->orderByDesc('cuenta_solicitada_at')
            ->get();

        // Devuelve SOLO el panel (HTML)
        return view('admin.caja.partials.pendientes', compact('comandasPendientes'));
    }
}
