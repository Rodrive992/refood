<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\ComandaItem;
use App\Models\CartaCategoria;
use App\Models\CartaItem;
use App\Models\Venta;
use App\Models\Pago;

class DashboardController extends Controller
{
    // =========================================================
    // Helpers
    // =========================================================
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_if((int)$mesa->id_local !== $this->localId(), 403, 'Mesa fuera de tu local.');
    }

    private function comandaActivaDeMesa(int $mesaId): ?Comanda
    {
        return Comanda::query()
            ->where('id_local', $this->localId())
            ->where('id_mesa', $mesaId)
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->latest('id')
            ->with(['items' => function ($q) {
                $q->orderBy('id');
            }])
            ->first();
    }

    private function calcSubtotal(?Comanda $comanda): float
    {
        if (!$comanda) return 0.0;

        return (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float)$it->precio_snapshot * (float)$it->cantidad);
    }

    // =========================================================
    // Dashboard
    // =========================================================
    public function index(Request $request)
    {
        $localId = $this->localId();
        $mesaId  = (int) $request->get('mesa_id', 0);

        $mesas = Mesa::query()
            ->where('id_local', $localId)
            ->orderByRaw("FIELD(estado,'ocupada','reservada','cerrando','libre','inactiva')")
            ->orderBy('nombre')
            ->get();

        $mesaActiva = null;
        $comandaActiva = null;
        $subtotal = 0.0;

        if ($mesaId > 0) {
            $mesaActiva = $mesas->firstWhere('id', $mesaId);
            if ($mesaActiva) {
                $comandaActiva = $this->comandaActivaDeMesa($mesaActiva->id);
                $subtotal = $this->calcSubtotal($comandaActiva);
            }
        }

        // Carta (solo activos del local)
        $cartaCategorias = CartaCategoria::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $cartaItems = CartaItem::query()
            ->where('id_local', $localId)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        // ✅ mapa mesa_id => comanda activa (para badge "cuenta")
        $comandasActivasPorMesa = Comanda::query()
            ->where('id_local', $localId)
            ->whereNotNull('id_mesa')
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada']) // activas para mozo
            ->latest('id')
            ->get()
            ->keyBy('id_mesa');

        return view('mozo.dashboard', compact(
            'mesas',
            'mesaActiva',
            'comandaActiva',
            'subtotal',
            'cartaCategorias',
            'cartaItems',
            'comandasActivasPorMesa' // ✅ nuevo
        ));
    }

    // =========================================================
    // Crear comanda para mesa
    // POST /mozo/mesas/{mesa}/comandas
    // route('mozo.comandas.createForMesa', $mesa)
    // =========================================================
    public function createForMesa(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $user = auth()->user();
        $localId = $this->localId();

        $comandaExistente = $this->comandaActivaDeMesa((int)$mesa->id);
        if ($comandaExistente) {
            return redirect()
                ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
                ->with('ok', 'Ya existe una comanda activa para esta mesa.');
        }

        DB::transaction(function () use ($mesa, $user, $localId) {
            // si estaba libre, la pasamos a ocupada (opcional)
            if (in_array($mesa->estado, ['libre', 'reservada'], true)) {
                $mesa->estado = 'ocupada';
                $mesa->save();
            }

            Comanda::create([
                'id_local'   => $localId,
                'id_mesa'    => $mesa->id,
                'id_mozo'    => $user->id,
                'estado'     => 'abierta',
                'observacion' => null,
                'opened_at'  => now(),
            ]);
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
            ->with('ok', 'Comanda creada.');
    }

    // =========================================================
    // Agregar ITEMS (MULTI)
    // POST /mozo/comandas/{comanda}/items
    // route('mozo.comandas.items.add', $comanda)
    // =========================================================
    public function addItems(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        abort_if((int)$comanda->id_local !== $localId, 403, 'Comanda fuera de tu local.');

        // estado válido
        abort_if(!in_array($comanda->estado, ['abierta', 'en_cocina', 'lista', 'entregada'], true), 422, 'La comanda no está activa.');

        // Compatibilidad:
        // - nuevo: items[]
        // - viejo: id_item/cantidad/nota
        $payload = $request->input('items');

        if (empty($payload)) {
            $payload = [[
                'id_item'  => $request->input('id_item'),
                'cantidad' => $request->input('cantidad', 1),
                'nota'     => $request->input('nota'),
            ]];
        }

        // Validación manual sobre el payload normalizado
        $request->merge(['items' => $payload]);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_item' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.nota' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Seleccioná al menos un item.',
            'items.*.id_item.required' => 'Falta un item.',
            'items.*.cantidad.min' => 'La cantidad mínima es 0,01.',
        ]);

        DB::transaction(function () use ($validated, $comanda, $localId) {

            // Traemos todos los carta_items necesarios, asegurando id_local y activo
            $ids = collect($validated['items'])->pluck('id_item')->map(fn($v) => (int)$v)->unique()->values();

            $itemsDB = CartaItem::query()
                ->where('id_local', $localId)
                ->where('activo', 1)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $row) {
                $idItem = (int) $row['id_item'];
                $cantidad = (float) $row['cantidad'];
                $nota = $row['nota'] ?? null;

                $ci = $itemsDB->get($idItem);
                abort_if(!$ci, 422, "El item ID {$idItem} no existe, no es del local o está inactivo.");

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

            // Si querés, podés marcar comanda "abierta" sí o sí al agregar items
            if ($comanda->estado !== 'abierta') {
                $comanda->estado = 'abierta';
                $comanda->save();
            }
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Items agregados a la comanda.');
    }

    // =========================================================
    // Cobrar comanda: crea venta + pagos, cierra comanda y mesa
    // POST /mozo/comandas/{comanda}/cobrar
    // route('mozo.comandas.cobrar', $comanda)
    // =========================================================
    public function cobrar(Request $request, Comanda $comanda)
    {
        $localId = $this->localId();
        $user = auth()->user();

        abort_if((int)$comanda->id_local !== $localId, 403, 'Comanda fuera de tu local.');
        abort_if(!in_array($comanda->estado, ['abierta', 'en_cocina', 'lista', 'entregada'], true), 422, 'La comanda no está activa.');

        $validated = $request->validate([
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'recargo'   => ['nullable', 'numeric', 'min:0'],
            'nota'      => ['nullable', 'string', 'max:255'],

            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.tipo' => ['required', 'in:efectivo,debito,transferencia'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0.01'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:120'],
        ], [
            'pagos.required' => 'Agregá al menos un pago.',
            'pagos.*.monto.min' => 'El monto mínimo por pago es 0,01.',
        ]);

        $descuento = (float) ($validated['descuento'] ?? 0);
        $recargo = (float) ($validated['recargo'] ?? 0);

        // subtotal por snapshot
        $comanda->load('items');
        $subtotal = (float) $comanda->items
            ->where('estado', '!=', 'anulado')
            ->sum(fn($it) => (float)$it->precio_snapshot * (float)$it->cantidad);

        $total = max(0, $subtotal - $descuento + $recargo);

        $pagado = 0.0;
        foreach ($validated['pagos'] as $p) {
            $pagado += (float)$p['monto'];
        }

        abort_if($pagado + 0.00001 < $total, 422, 'El total pagado es menor al total a cobrar.');

        $vuelto = max(0, $pagado - $total);

        $venta = null;

        DB::transaction(function () use (
            &$venta,
            $validated,
            $localId,
            $user,
            $comanda,
            $subtotal,
            $descuento,
            $recargo,
            $total,
            $pagado,
            $vuelto
        ) {
            // Crear venta
            $venta = Venta::create([
                'id_local'     => $localId,
                'id_comanda'   => $comanda->id,
                'id_mesa'      => $comanda->id_mesa,
                'id_mozo'      => $user->id,
                'estado'       => 'pagada',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'recargo'      => $recargo,
                'total'        => $total,
                'pagado_total' => $pagado,
                'vuelto'       => $vuelto,
                'nota'         => $validated['nota'] ?? null,
                'sold_at'      => now(),
            ]);

            // Pagos
            foreach ($validated['pagos'] as $p) {
                Pago::create([
                    'id_venta'   => $venta->id,
                    'tipo'       => $p['tipo'],
                    'monto'      => (float) $p['monto'],
                    'referencia' => $p['referencia'] ?? null,
                    'recibido_at' => now(),
                ]);
            }

            // Cerrar comanda
            $comanda->estado = 'cerrada';
            $comanda->closed_at = now();
            $comanda->save();

            // Mesa -> libre (o cerrando si preferís)
            if ($comanda->id_mesa) {
                $mesa = Mesa::query()
                    ->where('id', $comanda->id_mesa)
                    ->where('id_local', $localId)
                    ->first();

                if ($mesa) {
                    $mesa->estado = 'libre';
                    $mesa->observacion = null;
                    $mesa->save();
                }
            }
        });

        // Luego del cobro, volvemos al dashboard sin mesa seleccionada
        // o a la misma mesa (ya libre). Yo lo dejo al dashboard sin mesa.
        return redirect()
            ->route('mozo.dashboard')
            ->with('ok', 'Cobro confirmado. Venta #' . ($venta->id ?? '—') . '.');
    }

    public function mesasPartial(Request $request)
    {
        $localId = $this->localId();

        $mesas = Mesa::query()
            ->where('id_local', $localId)
            ->orderByRaw("FIELD(estado,'ocupada','reservada','libre','inactiva','fuera_servicio')")
            ->orderBy('nombre')
            ->get();

        $comandasActivasPorMesa = Comanda::query()
            ->where('id_local', $localId)
            ->whereNotNull('id_mesa')
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->latest('id')
            ->get()
            ->keyBy('id_mesa');

        return view('mozo.partials.mesas', compact('mesas', 'comandasActivasPorMesa'));
    }
}
