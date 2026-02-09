{{-- resources/views/mozo/partials/cuenta.blade.php --}}
@php
    // Entrada esperada:
    // $isMobile (bool)
    // $mesaSelected (?Mesa)
    // $comanda (?Comanda)  (puede venir null)
    // $subtotal (float)

    $mesa = $mesaSelected ?? null;
    $hasMesa = $mesa && (int)$mesa->id > 0;

    $hasComanda = $comanda && (int)$comanda->id > 0;
    $cuentaPedida = $hasComanda && (int)($comanda->cuenta_solicitada ?? 0) === 1;

    $estadoMesa = $hasMesa ? (string)($mesa->estado ?? '') : '';
    $mesaLibre = $estadoMesa === 'libre';

    $subtotalFmt = number_format((float)($subtotal ?? 0), 0, '.', '.');
@endphp

<div class="bg-white rounded-2xl border shadow-sm overflow-hidden"
     style="border-color: var(--rf-border);">

    {{-- Header --}}
    <div class="p-4 border-b flex items-start justify-between gap-3"
         style="border-color: var(--rf-border);">
        <div>
            <div class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                Cuenta
            </div>
            <div class="text-lg font-extrabold leading-tight" style="color: var(--rf-text);">
                @if($hasMesa)
                    {{ $mesa->nombre }}
                @else
                    Seleccioná una mesa
                @endif
            </div>
            @if($hasMesa)
                <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                    Estado mesa:
                    <span class="font-semibold" style="color: var(--rf-text);">{{ $estadoMesa }}</span>
                    @if($hasComanda)
                        • Comanda #{{ $comanda->id }}
                    @endif
                </div>
            @endif
        </div>

        {{-- Badge estado cuenta --}}
        @if($hasComanda)
            @if($cuentaPedida)
                <div class="px-3 py-1.5 rounded-xl text-xs font-bold border"
                     style="border-color: var(--rf-border); background: rgba(245,158,11,0.12); color: var(--rf-warning);">
                    Cuenta solicitada
                </div>
            @else
                <div class="px-3 py-1.5 rounded-xl text-xs font-bold border"
                     style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);">
                    Sin solicitar
                </div>
            @endif
        @endif
    </div>

    {{-- Body --}}
    <div class="p-4 space-y-4">
        {{-- Empty state --}}
        @if(!$hasMesa)
            <div class="rounded-2xl border p-4 text-sm"
                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                Elegí una mesa para ver el subtotal y solicitar la cuenta.
            </div>
        @else
            {{-- Subtotal card --}}
            <div class="rounded-2xl border p-4 flex items-center justify-between gap-3"
                 style="border-color: var(--rf-border); background: var(--rf-bg);">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                        Subtotal estimado
                    </div>
                    <div class="text-2xl font-extrabold" style="color: var(--rf-text);">
                        {{ $subtotalFmt }}
                    </div>
                </div>

                <div class="text-right">
                    @if($hasComanda)
                        <div class="text-xs" style="color: var(--rf-text-light);">
                            Estado comanda: <span class="font-semibold" style="color: var(--rf-text);">{{ $comanda->estado }}</span>
                        </div>
                        @if($cuentaPedida)
                            <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                Pedido: {{ optional($comanda->cuenta_solicitada_at)->format('H:i') ?? '—' }}
                            </div>
                        @endif
                    @else
                        <div class="text-xs" style="color: var(--rf-text-light);">
                            No hay comanda activa
                        </div>
                    @endif
                </div>
            </div>

            {{-- Acciones --}}
            <div class="grid gap-2">
                {{-- Si la mesa está libre, no se puede pedir cuenta --}}
                @if($mesaLibre)
                    <button type="button" disabled
                            class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                            style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);">
                        Mesa libre (no se puede solicitar cuenta)
                    </button>
                @else
                    @if(!$hasComanda)
                        <button type="button" disabled
                                class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                                style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);">
                            Sin comanda activa
                        </button>
                    @else
                        @if($cuentaPedida)
                            <button type="button" disabled
                                    class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                                    style="border-color: var(--rf-border); background: rgba(245,158,11,0.12); color: var(--rf-warning);">
                                Cuenta ya solicitada
                            </button>

                            {{-- Info de caja --}}
                            <div class="rounded-2xl border p-4 text-sm"
                                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                                La cuenta ya fue enviada a caja. Hasta que caja cierre la comanda, no se pueden agregar/editar items.
                            </div>
                        @else
                            {{-- Botón que abre modal --}}
                            <button type="button"
                                    data-action="open-solicitar-cuenta"
                                    class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold rf-hover-lift"
                                    style="background: var(--rf-primary); color: white;">
                                Solicitar cuenta a caja
                            </button>

                            <div class="text-xs" style="color: var(--rf-text-light);">
                                Podés dejar una nota para caja (ej: “paga con transferencia”, “separar bebidas”).
                            </div>

                            {{-- Modal solicitar cuenta --}}
                            @push('modals')
                                <div id="modalSolicitarCuenta" class="rf-modal-backdrop hidden fixed inset-0 z-50 items-end md:items-center justify-center"
                                     style="background: rgba(0,0,0,0.45);">
                                    <div class="w-full md:max-w-lg md:rounded-3xl md:mx-4 rounded-t-3xl bg-white border animate-fade-in"
                                         style="border-color: var(--rf-border);">
                                        <div class="p-4 border-b flex items-center justify-between"
                                             style="border-color: var(--rf-border);">
                                            <div>
                                                <div class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                                                    Solicitar cuenta
                                                </div>
                                                <div class="text-lg font-extrabold" style="color: var(--rf-text);">
                                                    {{ $mesa->nombre }} • Comanda #{{ $comanda->id }}
                                                </div>
                                            </div>
                                            <button type="button"
                                                    data-action="close-modal"
                                                    data-modal="modalSolicitarCuenta"
                                                    class="h-10 w-10 rounded-2xl border flex items-center justify-center rf-hover-lift"
                                                    style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                                                ✕
                                            </button>
                                        </div>

                                        <form method="POST" action="{{ route('mozo.comandas.solicitarCuenta', $comanda) }}" class="p-4 space-y-4">
                                            @csrf

                                            <div class="rounded-2xl border p-4"
                                                 style="border-color: var(--rf-border); background: var(--rf-bg);">
                                                <div class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                                                    Total estimado
                                                </div>
                                                <div class="text-2xl font-extrabold" style="color: var(--rf-text);">
                                                    {{ $subtotalFmt }}
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-bold mb-2" style="color: var(--rf-text);">
                                                    Nota para caja (opcional)
                                                </label>
                                                <input type="text" name="nota" maxlength="255"
                                                       class="w-full rounded-2xl border px-4 py-3 text-sm focus:outline-none"
                                                       style="border-color: var(--rf-border); background: white;"
                                                       placeholder="Ej: paga con transferencia / separar bebidas">
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        data-action="close-modal"
                                                        data-modal="modalSolicitarCuenta"
                                                        class="flex-1 px-4 py-3 rounded-2xl text-sm font-extrabold border"
                                                        style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                                                    Cancelar
                                                </button>

                                                <button type="submit"
                                                        class="flex-1 px-4 py-3 rounded-2xl text-sm font-extrabold rf-hover-lift"
                                                        style="background: var(--rf-primary); color: white;">
                                                    Confirmar
                                                </button>
                                            </div>

                                            <div class="text-xs" style="color: var(--rf-text-light);">
                                                Al solicitar la cuenta, la comanda queda bloqueada para cambios hasta que caja la cierre.
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endpush
                        @endif
                    @endif
                @endif
            </div>

            {{-- Botón liberar (opcional, pero útil para mozo) --}}
            <div class="pt-2 border-t" style="border-color: var(--rf-border);">
                <form method="POST" action="{{ route('mozo.mesas.liberar', $mesa) }}"
                      onsubmit="return confirm('¿Liberar {{ $mesa->nombre }}? Se anula la comanda activa (si existe).');">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border rf-hover-lift"
                            style="border-color: var(--rf-border); background: rgba(239,68,68,0.08); color: var(--rf-error);">
                        Liberar mesa
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
