{{-- resources/views/mozo/partials/cuenta.blade.php --}}
@php
    $mesa = $mesaSelected ?? null;
    $hasMesa = $mesa && (int)$mesa->id > 0;

    $hasComanda = $comanda && (int)$comanda->id > 0;
    $cuentaPedida = $hasComanda && (int)($comanda->cuenta_solicitada ?? 0) === 1;

    $estadoMesa = $hasMesa ? (string)($mesa->estado ?? '') : '';
    $mesaLibre = $estadoMesa === 'libre';

    $mozoId = $mozoId ?? auth()->id();
    $mesaTomadaPorOtro = $hasMesa
        && $estadoMesa !== 'libre'
        && !empty($mesa->atendida_por)
        && (int)$mesa->atendida_por !== (int)$mozoId;

    $nombreMozo = $mesa?->mozoAtendiendo?->name;

    $subtotalNumero = (float)($subtotal ?? 0);
    $subtotalFmt = number_format($subtotalNumero, 0, '.', '.');

    $preticketPendiente = $hasComanda && (int)($comanda->preticket_pendiente ?? 0) === 1;
    $preticketImpresoAt = !empty($comanda?->preticket_impreso_at)
        ? \Carbon\Carbon::parse($comanda->preticket_impreso_at)->format('H:i')
        : null;
@endphp

<div class="bg-white rounded-2xl border shadow-sm overflow-hidden"
     style="border-color: var(--rf-border);">

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

                @if($nombreMozo && !$mesaLibre)
                    <div class="text-xs mt-2 font-semibold"
                         style="color: {{ $mesaTomadaPorOtro ? '#dc2626' : '#15803d' }};">
                        {{ $mesaTomadaPorOtro ? 'Atiende:' : 'Mozo asignado:' }} {{ $nombreMozo }}
                    </div>
                @endif
            @endif
        </div>

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

    <div class="p-4 space-y-4">
        @if(!$hasMesa)
            <div class="rounded-2xl border p-4 text-sm"
                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                Elegí una mesa para ver el subtotal y solicitar la cuenta.
            </div>
        @else
            <div class="rounded-2xl border p-4 flex items-center justify-between gap-3"
                 style="border-color: var(--rf-border); background: var(--rf-bg);">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                        Subtotal estimado
                    </div>
                    <div class="text-2xl font-extrabold" style="color: var(--rf-text);">
                        ${{ $subtotalFmt }}
                    </div>
                </div>

                <div class="text-right">
                    @if($hasComanda)
                        <div class="text-xs" style="color: var(--rf-text-light);">
                            Estado comanda:
                            <span class="font-semibold" style="color: var(--rf-text);">
                                {{ $comanda->estado }}
                            </span>
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

            <div class="grid gap-2">
                @if($mesaLibre)
                    <button type="button" disabled
                            class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                            style="border-color: var(--rf-border); background: var(--rf-border-light); color: var(--rf-text-light);">
                        Mesa libre (no se puede solicitar cuenta)
                    </button>
                @elseif($mesaTomadaPorOtro)
                    <button type="button" disabled
                            class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                            style="border-color: var(--rf-border); background: rgba(239,68,68,0.10); color: #dc2626;">
                        Bloqueada por otro mozo
                    </button>

                    <div class="rounded-2xl border p-4 text-sm"
                         style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                        No podés solicitar la cuenta porque esta mesa está siendo atendida por {{ $nombreMozo ?? 'otro mozo' }}.
                    </div>
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

                            @if($preticketPendiente)
                                <button type="button" disabled
                                        class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold border"
                                        style="border-color: var(--rf-primary); background: rgba(249,115,22,0.10); color: var(--rf-primary);">
                                    Preticket enviado a caja
                                </button>
                            @else
                                <form method="POST"
                                      action="{{ route('mozo.comandas.pedirPreticket', $comanda) }}"
                                      class="w-full">
                                    @csrf
                                    <button type="submit"
                                            class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold rf-hover-lift border"
                                            style="border-color: var(--rf-primary); background: white; color: var(--rf-primary);">
                                        Imprimir preticket en caja
                                    </button>
                                </form>
                            @endif

                            <div class="rounded-2xl border p-4 text-sm"
                                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                                La cuenta ya fue enviada a caja. Hasta que caja cierre la comanda, no se pueden agregar/editar items.
                                <br>
                                @if($preticketPendiente)
                                    El preticket fue solicitado y está pendiente de impresión en caja.
                                @elseif($preticketImpresoAt)
                                    Última impresión de preticket registrada a las {{ $preticketImpresoAt }}.
                                @else
                                    Podés pedir el preticket para que se imprima en la computadora de caja conectada a la impresora.
                                @endif
                            </div>
                        @else
                            <button type="button"
                                    data-action="open-solicitar-cuenta"
                                    data-comanda-id="{{ $comanda->id }}"
                                    data-mesa-nombre="{{ $mesa->nombre }}"
                                    data-subtotal-fmt="{{ $subtotalFmt }}"
                                    data-action-url="{{ route('mozo.comandas.solicitarCuenta', $comanda) }}"
                                    class="w-full px-4 py-3 rounded-2xl text-sm font-extrabold rf-hover-lift"
                                    style="background: var(--rf-primary); color: white;">
                                Solicitar cuenta a caja
                            </button>

                            <div class="text-xs" style="color: var(--rf-text-light);">
                                Podés dejar una nota para caja (ej: “paga con transferencia”, “separar bebidas”).
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>