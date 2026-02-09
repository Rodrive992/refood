{{-- resources/views/mozo/partials/comanda.blade.php --}}
@php
    $isMobile = $isMobile ?? false;
    $mesa = $mesaSelected ?? null;
    $com = $comanda ?? null;
    $sub = $subtotal ?? 0;
@endphp

<div class="bg-white rounded-2xl border rf-scrollbar" style="border-color: var(--rf-border);">
    <div class="p-4 flex items-start justify-between gap-3">
        <div>
            <h2 class="font-bold text-lg">Comanda</h2>

            @if(!$mesa)
                <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                    Seleccioná una mesa para ver su comanda.
                </p>
            @else
                <div class="mt-1 flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold" style="color: var(--rf-text);">
                        Mesa: {{ $mesa->nombre }}
                    </span>

                    @php
                        $estadoMesa = $mesa->estado ?? '';
                        $badgeMesa = match($estadoMesa){
                            'ocupada' => ['bg'=>'var(--rf-primary-soft)','tx'=>'var(--rf-primary-hover)','label'=>'Ocupada'],
                            'reservada' => ['bg'=>'rgba(59,130,246,0.12)','tx'=>'var(--rf-info)','label'=>'Reservada'],
                            'libre' => ['bg'=>'var(--rf-secondary-soft)','tx'=>'var(--rf-secondary-hover)','label'=>'Libre'],
                            default => ['bg'=>'rgba(107,114,128,0.12)','tx'=>'var(--rf-text-light)','label'=>ucfirst($estadoMesa)],
                        };
                    @endphp

                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs"
                          style="background: {{ $badgeMesa['bg'] }}; color: {{ $badgeMesa['tx'] }};">
                        {{ $badgeMesa['label'] }}
                    </span>

                    @if(!empty($mesa->observacion))
                        <span class="text-xs px-2 py-1 rounded-full"
                              style="background: var(--rf-border-light); color: var(--rf-text-light);">
                            {{ $mesa->observacion }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($mesa)
            <div class="flex items-center gap-2">
                @if(($mesa->estado ?? '') === 'libre')
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                        style="background: var(--rf-primary); color: white;"
                        data-action="ocupar"
                        data-mesa-id="{{ $mesa->id }}"
                        data-mesa-nombre="{{ $mesa->nombre }}"
                    >
                        Ocupar
                    </button>
                @else
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                        style="background: var(--rf-secondary); color: white;"
                        data-action="add-items"
                        data-mesa-id="{{ $mesa->id }}"
                    >
                        Agregar items
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="px-4 pb-4">
        @if(!$mesa)
            <div class="rounded-2xl border p-4 text-sm"
                 style="border-color: var(--rf-border); background: var(--rf-bg); color: var(--rf-text-light);">
                👈 Elegí una mesa para empezar.
            </div>
        @else
            {{-- Resumen comanda --}}
            <div class="rounded-2xl border p-4"
                 style="border-color: var(--rf-border); background: var(--rf-bg);">
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <div class="font-semibold" style="color: var(--rf-text);">
                            {{ $com ? 'Comanda #' . $com->id : 'Sin comanda aún' }}
                        </div>
                        <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                            {{ $com ? ('Estado: ' . ucfirst(str_replace('_',' ', $com->estado))) : 'Agregá items para crearla automáticamente.' }}
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-xs" style="color: var(--rf-text-light);">Subtotal</div>
                        <div class="text-lg font-extrabold" style="color: var(--rf-text);">
                            {{ number_format((float)$sub, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if($com && (int)($com->cuenta_solicitada ?? 0) === 1)
                    <div class="mt-3 text-xs px-3 py-2 rounded-xl"
                         style="background: rgba(245,158,11,0.12); color: var(--rf-warning);">
                        Cuenta solicitada. La comanda queda bloqueada hasta que Caja la cierre.
                    </div>
                @endif

                @if($com && (int)($com->cuenta_solicitada ?? 0) === 0)
                    <div class="mt-3 flex items-center justify-end">
                        <button type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold rf-hover-lift"
                            style="background: var(--rf-primary); color: white;"
                            data-action="open-solicitar-cuenta"
                            data-comanda-id="{{ $com->id }}"
                        >
                            Solicitar cuenta
                        </button>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="mt-4">
                <h3 class="font-bold text-sm mb-2" style="color: var(--rf-text);">Items</h3>

                @if(!$com || $com->items->count() === 0)
                    <div class="rounded-2xl border p-4 text-sm"
                         style="border-color: var(--rf-border); color: var(--rf-text-light);">
                        No hay items todavía.
                        @if(($mesa->estado ?? '') !== 'libre')
                            Tocá <b>Agregar items</b>.
                        @else
                            Primero <b>Ocupar</b>.
                        @endif
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($com->items as $it)
                            <div class="rounded-2xl border p-3"
                                 style="border-color: var(--rf-border);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-semibold truncate" style="color: var(--rf-text);">
                                            {{ $it->nombre_snapshot }}
                                        </div>

                                        @if(!empty($it->nota))
                                            <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                                Nota: {{ $it->nota }}
                                            </div>
                                        @endif

                                        <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                                            Estado: {{ ucfirst(str_replace('_',' ', $it->estado)) }}
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="text-xs" style="color: var(--rf-text-light);">Cant.</div>
                                        <div class="font-bold" style="color: var(--rf-text);">
                                            {{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}
                                        </div>

                                        <div class="text-xs mt-2" style="color: var(--rf-text-light);">Total</div>
                                        <div class="font-extrabold" style="color: var(--rf-text);">
                                            {{ number_format((float)$it->precio_snapshot * (float)$it->cantidad, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- acciones item (solo si NO pidió cuenta) --}}
                                @if($com && (int)($com->cuenta_solicitada ?? 0) === 0)
                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('mozo.comandas.items.delete', $it) }}"
                                              onsubmit="return confirm('¿Eliminar este item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl text-xs font-semibold border rf-hover-lift"
                                                style="border-color: var(--rf-border); color: var(--rf-error);">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Carta rápida (solo mobile, opcional) --}}
            @if($isMobile && ($mesa->estado ?? '') !== 'libre')
                <div class="mt-4 rounded-2xl border p-4"
                     style="border-color: var(--rf-border); background: var(--rf-bg);">
                    <div class="text-sm font-bold" style="color: var(--rf-text);">
                        Agregar rápido
                    </div>
                    <div class="text-xs mt-1" style="color: var(--rf-text-light);">
                        Usá el botón “Agregar items” para buscar por categoría y cargar cantidades.
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modal solicitar cuenta: simple (sin carpeta extra) --}}
@if($com)
<div id="modalSolicitarCuenta" class="hidden fixed inset-0 z-50 items-center justify-center rf-modal-backdrop"
     style="background: rgba(0,0,0,0.45);">
    <div class="bg-white w-[92%] max-w-lg rounded-2xl shadow-xl border p-4 animate-fade-in"
         style="border-color: var(--rf-border);">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-lg">Solicitar cuenta</h3>
            <button class="p-2 rounded-xl border"
                style="border-color: var(--rf-border);"
                data-action="close-modal" data-modal="modalSolicitarCuenta">✕</button>
        </div>

        <form method="POST" action="{{ route('mozo.comandas.solicitarCuenta', $com) }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="text-sm font-semibold" style="color: var(--rf-text);">Nota (opcional)</label>
                <input type="text" name="nota"
                       class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
                       style="border-color: var(--rf-border);"
                       placeholder="Ej: pagar con transferencia / dividir cuenta...">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button"
                    class="px-4 py-2 rounded-xl text-sm font-semibold border"
                    style="border-color: var(--rf-border);"
                    data-action="close-modal" data-modal="modalSolicitarCuenta">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-sm font-semibold"
                    style="background: var(--rf-primary); color: white;">
                    Enviar a caja
                </button>
            </div>
        </form>
    </div>
</div>
@endif
