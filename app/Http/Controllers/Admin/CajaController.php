<?php
// app/Http/Controllers/Admin/CajaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\Venta;
use App\Models\Pago;
use App\Models\Caja;
use App\Models\CartaCategoria;
use App\Models\CartaItem;
use App\Models\ComandaItem;
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

    private function cajaAbierta(int $localId): ?Caja
    {
        return Caja::query()
            ->where('id_local', $localId)
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();
    }

    private function propinasTurno(?Caja $caja): float
    {
        if (!$caja) {
            return 0.0;
        }

        return (float) Venta::query()
            ->where('id_caja', $caja->id)
            ->sum('propina');
    }

    private function getMesasConPendientes(int $localId): array
    {
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

        $pendientesPorMesa = $comandasPendientes->keyBy('id_mesa');

        return [$mesas, $comandasPendientes, $pendientesPorMesa];
    }

    public function index(Request $request)
    {
        $localId = $this->localId();
        $cajaAbierta = $this->cajaAbierta($localId);

        if ($cajaAbierta) {
            $cajaAbierta->refreshTotalesCache();
            $cajaAbierta->refresh();
        }

        $propinasTurno = $this->propinasTurno($cajaAbierta);

        [$mesas, $comandasPendientes, $pendientesPorMesa] = $this->getMesasConPendientes($localId);

        return view('admin.caja.index', compact(
            'mesas',
            'comandasPendientes',
            'pendientesPorMesa',
            'cajaAbierta',
            'propinasTurno'
        ));
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

        if (in_array($comanda->estado, ['cerrada', 'anulada'], true)) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'La comanda ya está cerrada/anulada.');
        }

        $comanda->load([
            'mesa',
            'mozo',
            'items' => fn($q) => $q->orderBy('id'),
        ]);

        $subtotal = (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);

        $cartaCategorias = CartaCategoria::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $cartaItems = CartaItem::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('id_categoria')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'id_categoria', 'nombre', 'precio']);

        $cajaAbierta = $this->cajaAbierta($localId);

        if ($cajaAbierta) {
            $cajaAbierta->refreshTotalesCache();
            $cajaAbierta->refresh();
        }

        return view('admin.caja.show', compact('comanda', 'subtotal', 'cartaCategorias', 'cartaItems', 'cajaAbierta'));
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

    public function addItems(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int)$comanda->id_local === $localId, 403);

        abort_if(in_array($comanda->estado, ['cerrada', 'anulada'], true), 422, 'La comanda está cerrada/anulada.');

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'Caja solo modifica items cuando la cuenta fue solicitada.');
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_item' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.nota' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $comanda, $localId) {
            $ids = collect($data['items'])
                ->pluck('id_item')
                ->map(fn($v) => (int)$v)
                ->unique()
                ->values();

            $itemsDB = CartaItem::query()
                ->where('id_local', $localId)
                ->where('activo', 1)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($data['items'] as $row) {
                $idItem = (int) $row['id_item'];
                $cantidad = (float) $row['cantidad'];
                $nota = $row['nota'] ?? null;

                $ci = $itemsDB->get($idItem);
                abort_if(!$ci, 422, "El item ID {$idItem} no existe en tu carta o está inactivo.");

                ComandaItem::create([
                    'id_comanda'      => $comanda->id,
                    'id_item'         => $ci->id,
                    'nombre_snapshot' => $ci->nombre,
                    'precio_snapshot' => $ci->precio,
                    'cantidad'        => $cantidad,
                    'nota'            => $nota,
                    'estado'          => 'pendiente',
                ]);
            }

            $subtotalNuevo = (float) ComandaItem::query()
                ->where('id_comanda', $comanda->id)
                ->where('estado', '!=', 'anulado')
                ->sum(DB::raw('precio_snapshot * cantidad'));

            $comanda->update([
                'total_estimado' => $subtotalNuevo,
            ]);
        });

        return back()->with('ok', 'Items agregados por Caja.');
    }

    public function deleteItem(ComandaItem $comandaItem)
    {
        $localId = $this->localId();

        $comanda = Comanda::query()
            ->where('id', $comandaItem->id_comanda)
            ->where('id_local', $localId)
            ->firstOrFail();

        abort_if(in_array($comanda->estado, ['cerrada', 'anulada'], true), 422, 'La comanda está cerrada/anulada.');

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            return redirect()
                ->route('admin.caja.index')
                ->with('error', 'Caja solo modifica items cuando la cuenta fue solicitada.');
        }

        DB::transaction(function () use ($comandaItem, $comanda) {
            $comandaItem->delete();

            $subtotalNuevo = (float) ComandaItem::query()
                ->where('id_comanda', $comanda->id)
                ->where('estado', '!=', 'anulado')
                ->sum(DB::raw('precio_snapshot * cantidad'));

            $comanda->update([
                'total_estimado' => $subtotalNuevo,
            ]);
        });

        return back()->with('ok', 'Item eliminado por Caja.');
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
            'propina'   => ['nullable', 'numeric', 'min:0'],
            'nota'      => ['nullable', 'string', 'max:255'],

            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.tipo' => ['required', 'in:efectivo,debito,transferencia'],
            'pagos.*.monto' => ['nullable', 'numeric', 'min:0'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:120'],
        ]);

        $pagos = collect($data['pagos'] ?? [])
            ->filter(fn($p) => (float)($p['monto'] ?? 0) > 0)
            ->values()
            ->all();

        if (count($pagos) === 0) {
            return back()
                ->withErrors(['pagos' => 'Ingresá al menos un pago con monto mayor a 0.'])
                ->withInput();
        }

        return DB::transaction(function () use ($comanda, $data, $pagos, $localId) {
            $caja = Caja::query()
                ->where('id_local', $localId)
                ->where('estado', 'abierta')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$caja) {
                return back()
                    ->withErrors(['caja' => 'No hay un turno de caja abierto. Abrí caja antes de cobrar.'])
                    ->withInput();
            }

            $subtotal = (float) $comanda->items()
                ->where('estado', '!=', 'anulado')
                ->sum(DB::raw('precio_snapshot * cantidad'));

            $descuento = (float) ($data['descuento'] ?? 0);
            $recargo   = (float) ($data['recargo'] ?? 0);
            $propina   = (float) ($data['propina'] ?? 0);

            $total = max(0, $subtotal - $descuento + $recargo);

            $pagadoTotal = 0.0;
            $efectivoRecibido = 0.0;

            foreach ($pagos as $p) {
                $m = (float) ($p['monto'] ?? 0);
                $pagadoTotal += $m;

                if (($p['tipo'] ?? '') === 'efectivo') {
                    $efectivoRecibido += $m;
                }
            }

            if ($pagadoTotal + 0.00001 < $total) {
                return back()
                    ->withErrors(['pagos' => 'El total pagado es menor al total a cobrar.'])
                    ->withInput();
            }

            $vuelto = max(0, $pagadoTotal - $total);

            if ($vuelto > $efectivoRecibido + 0.00001) {
                return back()
                    ->withErrors(['pagos' => 'El vuelto se entrega en efectivo. El pago en efectivo debe ser mayor o igual al vuelto.'])
                    ->withInput();
            }

            $ya = Venta::query()->where('id_comanda', $comanda->id)->first();
            if ($ya) {
                return redirect()
                    ->route('admin.caja.index')
                    ->with('error', 'Esta comanda ya fue cobrada.');
            }

            $venta = Venta::create([
                'id_local'     => $localId,
                'id_caja'      => $caja->id,
                'id_comanda'   => $comanda->id,
                'id_mesa'      => $comanda->id_mesa,
                'id_mozo'      => $comanda->id_mozo,
                'estado'       => 'pagada',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'recargo'      => $recargo,
                'propina'      => $propina,
                'total'        => $total,
                'pagado_total' => $pagadoTotal,
                'vuelto'       => $vuelto,
                'nota'         => $data['nota'] ?? null,
                'sold_at'      => now('America/Argentina/Buenos_Aires'),
            ]);

            foreach ($pagos as $p) {
                Pago::create([
                    'id_venta'    => $venta->id,
                    'tipo'        => $p['tipo'],
                    'monto'       => (float) $p['monto'],
                    'referencia'  => $p['referencia'] ?? null,
                    'recibido_at' => now('America/Argentina/Buenos_Aires'),
                ]);
            }

            $comanda->update([
                'estado'      => 'cerrada',
                'closed_at'   => now('America/Argentina/Buenos_Aires'),
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

            $caja->refreshTotalesCache();

            $printUrl = route('admin.ventas.ticket', $venta);

            return redirect()
                ->route('admin.caja.index')
                ->with('ok', 'Venta registrada. Enviando ticket a impresión...')
                ->with('rf_print_final_url', $printUrl)
                ->with('rf_venta_id', (int)$venta->id);
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

        return view('admin.caja.partials.pendientes', compact('comandasPendientes'));
    }

    public function pendientesPoll(Request $request)
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

        $html = view('admin.caja.partials.pendientes', compact('comandasPendientes'))->render();

        return response()->json([
            'ok' => true,
            'count' => (int)$comandasPendientes->count(),
            'html' => $html,
            'ts' => now()->toISOString(),
        ]);
    }

    public function mesasPoll(Request $request)
    {
        $localId = $this->localId();

        [$mesas, $comandasPendientes, $pendientesPorMesa] = $this->getMesasConPendientes($localId);

        $html = view('admin.caja.partials.mesas', compact('mesas', 'pendientesPorMesa'))->render();

        return response()->json([
            'ok' => true,
            'count' => (int)$mesas->count(),
            'html' => $html,
            'ts' => now()->toISOString(),
        ]);
    }

    public function preticketsPoll(Request $request)
    {
        $localId = $this->localId();

        $pendientes = Comanda::query()
            ->where('id_local', $localId)
            ->where('cuenta_solicitada', 1)
            ->where('preticket_pendiente', 1)
            ->whereNotIn('estado', ['cerrada', 'anulada'])
            ->with(['mesa', 'mozo'])
            ->orderBy('preticket_solicitado_at')
            ->get();

        $jobs = $pendientes->map(function ($comanda) {
            return [
                'id' => (int) $comanda->id,
                'mesa' => $comanda->mesa->nombre ?? 'Sin mesa',
                'mozo' => $comanda->mozo->name ?? ('#' . $comanda->id_mozo),
                'print_url' => route('admin.caja.cuenta.print', $comanda),
                'solicitado_at' => optional($comanda->preticket_solicitado_at)?->toISOString(),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'jobs' => $jobs,
            'count' => $jobs->count(),
            'ts' => now()->toISOString(),
        ]);
    }

    public function markPreticketPrinted(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        $comanda->update([
            'preticket_pendiente' => 0,
            'preticket_impreso_at' => now('America/Argentina/Buenos_Aires'),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Preticket marcado como impreso.',
        ]);
    }

    public function printPreticket(Comanda $comanda)
    {
        $localId = $this->localId();
        abort_unless((int) $comanda->id_local === $localId, 403);

        if ((int)($comanda->cuenta_solicitada ?? 0) !== 1) {
            abort(404, 'La cuenta no ha sido solicitada');
        }

        $comanda->load(['mesa', 'mozo', 'items' => fn($q) => $q->orderBy('id')]);

        $subtotal = (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float) $it->precio_snapshot * (float) $it->cantidad);

        return view('admin.caja.cuenta_print', compact('comanda', 'subtotal'));
    }
}