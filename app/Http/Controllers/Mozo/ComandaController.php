<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\ComandaItem;
use App\Models\CartaItem;

class ComandaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function mozoId(): int
    {
        return (int) auth()->id();
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_if((int) $mesa->id_local !== $this->localId(), 403, 'Mesa fuera de tu local.');
        abort_if(in_array($mesa->estado, ['inactiva', 'fuera_servicio'], true), 422, 'La mesa está inactiva.');
    }

    private function assertMesaTomadaPorEsteMozo(Mesa $mesa): void
    {
        $this->assertMesaLocal($mesa);

        abort_if($mesa->estado === 'libre', 422, 'Primero ocupá la mesa para poder operar.');
        abort_if(
            (int) ($mesa->atendida_por ?? 0) !== $this->mozoId(),
            403,
            'Esta mesa está siendo atendida por otro mozo.'
        );
    }

    private function assertComandaLocal(Comanda $comanda): void
    {
        abort_if((int) $comanda->id_local !== $this->localId(), 403, 'Comanda fuera de tu local.');
    }

    private function assertComandaOperablePorEsteMozo(Comanda $comanda): void
    {
        $this->assertComandaLocal($comanda);

        abort_if(empty($comanda->id_mesa), 422, 'La comanda no tiene una mesa asociada.');

        $mesa = Mesa::query()
            ->where('id', $comanda->id_mesa)
            ->where('id_local', $this->localId())
            ->firstOrFail();

        $this->assertMesaTomadaPorEsteMozo($mesa);
    }

    private function ensureComandaActiva(Comanda $comanda): void
    {
        abort_if(!in_array($comanda->estado, ['abierta', 'en_cocina', 'lista', 'entregada'], true), 422, 'La comanda no está activa.');
    }

    private function comandaActivaDeMesa(int $mesaId): ?Comanda
    {
        return Comanda::query()
            ->where('id_local', $this->localId())
            ->where('id_mesa', $mesaId)
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->latest('id')
            ->first();
    }

    private function normalizePayload(Request $request): array
    {
        $payload = $request->input('items');

        if (empty($payload)) {
            $payload = [[
                'id_item'  => $request->input('id_item'),
                'cantidad' => $request->input('cantidad', 1),
                'nota'     => $request->input('nota'),
            ]];
        }

        $request->merge(['items' => $payload]);

        return $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_item' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.nota' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function createForMesa(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $localId = $this->localId();
        $user = auth()->user();

        DB::transaction(function () use ($mesa, $user, $localId) {
            $mesa = Mesa::query()
                ->where('id', $mesa->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $mesa->estado !== 'libre' &&
                !empty($mesa->atendida_por) &&
                (int) $mesa->atendida_por !== (int) $user->id
            ) {
                abort(409, 'La mesa ya está siendo atendida por otro mozo.');
            }

            $ya = Comanda::query()
                ->where('id_local', $localId)
                ->where('id_mesa', $mesa->id)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($ya) {
                if ((int) ($mesa->atendida_por ?? 0) !== (int) $user->id) {
                    abort(409, 'La mesa ya tiene una comanda activa atendida por otro mozo.');
                }
                return;
            }

            if ($mesa->estado === 'libre') {
                $mesa->estado = 'ocupada';
            }

            $mesa->atendida_por = $user->id;
            $mesa->atendida_at  = $mesa->atendida_at ?? now();
            $mesa->save();

            Comanda::create([
                'id_local'          => $localId,
                'id_mesa'           => $mesa->id,
                'id_mozo'           => $user->id,
                'estado'            => 'abierta',
                'observacion'       => null,
                'opened_at'         => now(),
                'cuenta_solicitada' => 0,
            ]);
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
            ->with('ok', 'Comanda creada.');
    }

    public function addItemsForMesa(Request $request, Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $localId = $this->localId();
        $user = auth()->user();
        $validated = $this->normalizePayload($request);

        $comanda = null;

        DB::transaction(function () use (&$comanda, $validated, $mesa, $localId, $user) {
            $mesa = Mesa::query()
                ->where('id', $mesa->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($mesa->estado === 'libre', 422, 'Primero ocupá la mesa para poder agregar items.');

            abort_if(
                !empty($mesa->atendida_por) && (int) $mesa->atendida_por !== (int) $user->id,
                403,
                'Esta mesa está siendo atendida por otro mozo.'
            );

            $comanda = Comanda::query()
                ->where('id_local', $localId)
                ->where('id_mesa', $mesa->id)
                ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$comanda) {
                $mesa->atendida_por = $user->id;
                $mesa->atendida_at  = $mesa->atendida_at ?? now();
                $mesa->save();

                $comanda = Comanda::create([
                    'id_local'          => $localId,
                    'id_mesa'           => $mesa->id,
                    'id_mozo'           => $user->id,
                    'estado'            => 'abierta',
                    'observacion'       => null,
                    'opened_at'         => now(),
                    'cuenta_solicitada' => 0,
                ]);
            }

            abort_if(
                (int) ($mesa->atendida_por ?? 0) !== (int) $user->id,
                403,
                'Esta mesa está siendo atendida por otro mozo.'
            );

            abort_if((int)($comanda->cuenta_solicitada ?? 0) === 1, 422, 'Cuenta solicitada: solo administración puede agregar items.');

            $ids = collect($validated['items'])
                ->pluck('id_item')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            $itemsDB = CartaItem::query()
                ->where('id_local', $localId)
                ->where('activo', 1)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $row) {
                $idItem   = (int) $row['id_item'];
                $cantidad = (float) $row['cantidad'];
                $nota     = $row['nota'] ?? null;

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

            if ($comanda->estado !== 'abierta') {
                $comanda->estado = 'abierta';
                $comanda->save();
            }
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
            ->with('ok', 'Items agregados.');
    }

    public function addItem(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->ensureComandaActiva($comanda);
        $this->assertComandaOperablePorEsteMozo($comanda);

        abort_if((int)($comanda->cuenta_solicitada ?? 0) === 1, 422, 'Cuenta solicitada: solo administración puede agregar items.');

        $localId = $this->localId();
        $validated = $this->normalizePayload($request);

        DB::transaction(function () use ($validated, $comanda, $localId) {
            $comanda = Comanda::query()
                ->where('id', $comanda->id)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureComandaActiva($comanda);
            $this->assertComandaOperablePorEsteMozo($comanda);

            $ids = collect($validated['items'])
                ->pluck('id_item')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            $itemsDB = CartaItem::query()
                ->where('id_local', $localId)
                ->where('activo', 1)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $row) {
                $idItem   = (int) $row['id_item'];
                $cantidad = (float) $row['cantidad'];
                $nota     = $row['nota'] ?? null;

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

            if ($comanda->estado !== 'abierta') {
                $comanda->estado = 'abierta';
                $comanda->save();
            }
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Items agregados a la comanda.');
    }

    public function solicitarCuenta(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->ensureComandaActiva($comanda);
        $this->assertComandaOperablePorEsteMozo($comanda);

        $data = $request->validate([
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            return back()->with('ok', 'La cuenta ya fue solicitada.');
        }

        $subtotal = (float) $comanda->items()
            ->sum(DB::raw('precio_snapshot * cantidad'));

        DB::transaction(function () use ($comanda, $data, $subtotal) {
            $comanda = Comanda::query()
                ->where('id', $comanda->id)
                ->where('id_local', $this->localId())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureComandaActiva($comanda);
            $this->assertComandaOperablePorEsteMozo($comanda);

            $comanda->update([
                'cuenta_solicitada'      => 1,
                'cuenta_solicitada_at'   => now(),
                'cuenta_solicitada_por'  => auth()->id(),
                'cuenta_solicitada_nota' => $data['nota'] ?? null,
                'total_estimado'         => $subtotal,
                'estado_caja'            => 'pendiente',
            ]);
        });

        return back()->with('ok', 'Cuenta solicitada a caja.');
    }

    public function updateItem(Request $request, ComandaItem $comandaItem)
    {
        $localId = $this->localId();

        $comanda = Comanda::query()
            ->where('id', $comandaItem->id_comanda)
            ->where('id_local', $localId)
            ->firstOrFail();

        $this->ensureComandaActiva($comanda);
        $this->assertComandaOperablePorEsteMozo($comanda);

        abort_if((int)($comanda->cuenta_solicitada ?? 0) === 1, 422, 'Cuenta solicitada: solo administración puede modificar items.');

        $validated = $request->validate([
            'cantidad' => ['nullable', 'numeric', 'min:0.01'],
            'nota'     => ['nullable', 'string', 'max:255'],
            'estado'   => ['nullable', 'in:pendiente,en_cocina,listo,entregado'],
        ]);

        $comandaItem->fill($validated)->save();

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Item actualizado.');
    }

    public function setEstado(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->assertComandaOperablePorEsteMozo($comanda);

        $validated = $request->validate([
            'estado' => ['required', 'in:abierta,en_cocina,lista,entregada,cerrada,anulada'],
        ]);

        if (in_array($comanda->estado, ['cerrada', 'anulada'], true)) {
            abort(422, 'No se puede modificar una comanda cerrada/anulada.');
        }

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            abort(422, 'Cuenta solicitada: la comanda queda bloqueada hasta que Caja la cierre.');
        }

        $comanda->estado = $validated['estado'];
        $comanda->save();

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Estado de comanda actualizado.');
    }
}