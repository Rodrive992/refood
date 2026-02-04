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
    // =========================
    // Helpers
    // =========================
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local ?? null;
        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');
        return (int) $idLocal;
    }

    private function assertMesaLocal(Mesa $mesa): void
    {
        abort_if((int) $mesa->id_local !== $this->localId(), 403, 'Mesa fuera de tu local.');
    }

    private function assertComandaLocal(Comanda $comanda): void
    {
        abort_if((int) $comanda->id_local !== $this->localId(), 403, 'Comanda fuera de tu local.');
    }

    private function ensureComandaActiva(Comanda $comanda): void
    {
        // SOLO estados editables por mozo (sin "cerrando")
        abort_if(!in_array($comanda->estado, ['abierta', 'en_cocina', 'lista', 'entregada'], true), 422, 'La comanda no está activa.');
    }

    private function comandaActivaDeMesa(int $mesaId): ?Comanda
    {
        return Comanda::query()
            ->where('id_local', $this->localId())
            ->where('id_mesa', $mesaId)
            // ✅ sacar "cerrando" para no depender de un ENUM/estado que no existe
            ->whereIn('estado', ['abierta', 'en_cocina', 'lista', 'entregada'])
            ->latest('id')
            ->first();
    }

    private function calcularTotalEstimado(Comanda $comanda): float
    {
        return (float) $comanda->items()
            ->where('estado', '!=', 'anulado')
            ->get()
            ->sum(fn($it) => (float)$it->precio_snapshot * (float)$it->cantidad);
    }

    // =========================
    // Crear comanda para mesa
    // =========================
    public function createForMesa(Mesa $mesa)
    {
        $this->assertMesaLocal($mesa);

        $user = auth()->user();
        $localId = $this->localId();

        $ya = $this->comandaActivaDeMesa((int)$mesa->id);
        if ($ya) {
            return redirect()
                ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
                ->with('ok', 'Ya existe una comanda activa para esta mesa.');
        }

        DB::transaction(function () use ($mesa, $user, $localId) {

            if (in_array($mesa->estado, ['libre', 'reservada'], true)) {
                $mesa->estado = 'ocupada';
            }

            $mesa->atendida_por = $user->id;
            $mesa->atendida_at  = now();
            $mesa->save();

            Comanda::create([
                'id_local'   => $localId,
                'id_mesa'    => $mesa->id,
                'id_mozo'    => $user->id,
                'estado'     => 'abierta',
                'observacion' => null,
                'opened_at'  => now(),
                'cuenta_solicitada' => 0,
            ]);
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $mesa->id])
            ->with('ok', 'Comanda creada.');
    }

    // =========================
    // Agregar items (MULTI)
    // =========================
    public function addItem(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->ensureComandaActiva($comanda);

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            abort(422, 'Ya se solicitó la cuenta. No se pueden agregar más items.');
        }

        $localId = $this->localId();

        $payload = $request->input('items');
        if (empty($payload)) {
            $payload = [[
                'id_item'  => $request->input('id_item'),
                'cantidad' => $request->input('cantidad', 1),
                'nota'     => $request->input('nota'),
            ]];
        }

        $request->merge(['items' => $payload]);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_item' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.nota' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $comanda, $localId) {

            $ids = collect($validated['items'])
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

            foreach ($validated['items'] as $row) {
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

            if ($comanda->estado !== 'abierta') {
                $comanda->estado = 'abierta';
                $comanda->save();
            }
        });

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Items agregados a la comanda.');
    }

    // =========================
    // SOLICITAR CUENTA (MOZO)
    // =========================
    public function solicitarCuenta(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);
        $this->ensureComandaActiva($comanda);

        $data = $request->validate([
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            return back()->with('ok', 'La cuenta ya fue solicitada.');
        }

        // ✅ subtotal usando snapshot y sin anulados
        $subtotal = (float) $comanda->items()
            ->where('estado', '!=', 'anulado')
            ->sum(DB::raw('precio_snapshot * cantidad'));

        DB::transaction(function () use ($comanda, $data, $subtotal) {

            $comanda->update([
                'cuenta_solicitada'      => 1,
                'cuenta_solicitada_at'   => now(),
                'cuenta_solicitada_por'  => auth()->id(),
                'cuenta_solicitada_nota' => $data['nota'] ?? null,
                'total_estimado'         => $subtotal,
                'estado_caja'            => 'pendiente',
            ]);

            // ✅ IMPORTANTE: NO tocar mesa.estado = "cerrando"
            // La UI de "cerrando" la mostramos por cuenta_solicitada en la comanda.
        });

        return back()->with('ok', 'Cuenta solicitada a caja.');
    }

    // =========================
    // Update item
    // =========================
    public function updateItem(Request $request, ComandaItem $comandaItem)
    {
        $localId = $this->localId();

        $comanda = Comanda::query()
            ->where('id', $comandaItem->id_comanda)
            ->where('id_local', $localId)
            ->firstOrFail();

        $this->ensureComandaActiva($comanda);

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            abort(422, 'Ya se solicitó la cuenta. No se pueden editar items.');
        }

        $validated = $request->validate([
            'cantidad' => ['nullable', 'numeric', 'min:0.01'],
            'nota' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:pendiente,en_cocina,listo,entregado,anulado'],
        ]);

        $comandaItem->fill($validated)->save();

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Item actualizado.');
    }

    // =========================
    // Remove item (anular)
    // =========================
    public function removeItem(Request $request, ComandaItem $comandaItem)
    {
        $localId = $this->localId();

        $comanda = Comanda::query()
            ->where('id', $comandaItem->id_comanda)
            ->where('id_local', $localId)
            ->firstOrFail();

        $this->ensureComandaActiva($comanda);

        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            abort(422, 'Ya se solicitó la cuenta. No se pueden anular items.');
        }

        $comandaItem->estado = 'anulado';
        $comandaItem->save();

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Item anulado.');
    }

    // =========================
    // Set estado comanda (manual)
    // =========================
    public function setEstado(Request $request, Comanda $comanda)
    {
        $this->assertComandaLocal($comanda);

        // ✅ sacar "cerrando"
        $validated = $request->validate([
            'estado' => ['required', 'in:abierta,en_cocina,lista,entregada,cerrada,anulada'],
        ]);

        if (in_array($comanda->estado, ['cerrada', 'anulada'], true)) {
            abort(422, 'No se puede modificar una comanda cerrada/anulada.');
        }

        // ✅ si pidió cuenta, queda bloqueada hasta que Caja cierre
        if ((int)($comanda->cuenta_solicitada ?? 0) === 1) {
            abort(422, 'Ya se solicitó la cuenta. La comanda queda bloqueada hasta que Caja la cierre.');
        }

        $comanda->estado = $validated['estado'];
        $comanda->save();

        return redirect()
            ->route('mozo.dashboard', ['mesa_id' => $comanda->id_mesa])
            ->with('ok', 'Estado de comanda actualizado.');
    }
}
