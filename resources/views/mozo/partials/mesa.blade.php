{{-- resources/views/mozo/partials/mesa.blade.php --}}
@php
    $isMobile = $isMobile ?? false;
    $mozoId = $mozoId ?? auth()->id();
@endphp

<div class="bg-white rounded-2xl border" style="border-color: var(--rf-border);">
    <div class="p-4 flex items-center justify-between border-b" style="border-color: var(--rf-border);">
        <div>
            <h2 class="font-bold">Mesas</h2>
            <p class="text-sm" style="color: var(--rf-text-light);">Tocá una mesa para abrir su comanda</p>
        </div>
        <button onclick="location.reload()"
            class="px-3 py-2 rounded-xl text-sm border rf-hover-lift whitespace-nowrap"
            style="border-color: var(--rf-border);">
            Refrescar
        </button>
    </div>

    <div class="w-full overflow-x-auto rf-scrollbar" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
        <div class="p-4 flex gap-3" style="min-width: min-content;">
            @foreach($mesas as $m)
                @php
                    $isActive = $mesaSelected && (int)$mesaSelected->id === (int)$m->id;
                    $comanda = $comandasActivasPorMesa->get($m->id);
                    $estado = $m->estado;

                    $takenByOther = $estado !== 'libre'
                        && !empty($m->atendida_por)
                        && (int)$m->atendida_por !== (int)$mozoId;

                    $takenByMe = !empty($m->atendida_por)
                        && (int)$m->atendida_por === (int)$mozoId;

                    $nombreMozo = $m->mozoAtendiendo->name ?? null;

                    $badge = match($estado){
                        'ocupada' => ['bg' => 'var(--rf-primary-soft)', 'tx' => 'var(--rf-primary-hover)', 'label' => 'Ocupada'],
                        'reservada' => ['bg' => 'rgba(59,130,246,0.12)', 'tx' => 'var(--rf-info)', 'label' => 'Reservada'],
                        'libre' => ['bg' => 'var(--rf-secondary-soft)', 'tx' => 'var(--rf-secondary-hover)', 'label' => 'Libre'],
                        default => ['bg' => 'rgba(107,114,128,0.12)', 'tx' => 'var(--rf-text-light)', 'label' => ucfirst($estado)],
                    };

                    $cardWidth = $isMobile ? 'w-64' : 'w-72';
                @endphp

                <button
                    type="button"
                    data-action="select-mesa"
                    data-mesa-id="{{ $m->id }}"
                    class="flex-shrink-0 {{ $cardWidth }} text-left rounded-2xl border p-4 rf-transition-smooth rf-hover-lift {{ $isActive ? 'ring-2 ring-orange-400' : '' }} {{ $takenByOther ? 'opacity-80' : '' }}"
                    style="border-color: var(--rf-border); background: {{ $isActive ? 'var(--rf-primary-soft)' : 'var(--rf-white)' }};"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="font-extrabold text-lg truncate" style="color: var(--rf-text);" title="{{ $m->nombre }}">
                                {{ $m->nombre }}
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs whitespace-nowrap"
                                    style="background: {{ $badge['bg'] }}; color: {{ $badge['tx'] }};">
                                    {{ $badge['label'] }}
                                </span>

                                @if($takenByOther)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs whitespace-nowrap"
                                          style="background: rgba(239,68,68,0.12); color: #dc2626;">
                                        Otro mozo
                                    </span>
                                @elseif($takenByMe && $estado !== 'libre')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs whitespace-nowrap"
                                          style="background: rgba(22,163,74,0.12); color: #15803d;">
                                        Tu mesa
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($isActive)
                            <div class="flex-shrink-0 text-xs font-bold px-2 py-1 rounded-full whitespace-nowrap"
                                style="background: var(--rf-primary); color: white;">
                                Activa
                            </div>
                        @endif
                    </div>

                    @if($takenByOther && $nombreMozo)
                        <div class="mt-3 text-xs font-semibold" style="color: #dc2626;">
                            Atiende: {{ $nombreMozo }}
                        </div>
                    @elseif($takenByMe && $nombreMozo)
                        <div class="mt-3 text-xs font-semibold" style="color: #15803d;">
                            Atendés vos: {{ $nombreMozo }}
                        </div>
                    @endif

                    @if(!empty($m->observacion))
                        <div class="mt-2 text-xs line-clamp-2" style="color: var(--rf-text-light);" title="{{ $m->observacion }}">
                            {{ $m->observacion }}
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: var(--rf-border);">
                        <span style="color: var(--rf-text-light);">Comanda activa</span>
                        <span class="font-semibold" style="color: var(--rf-text);">
                            @if($comanda)
                                #{{ $comanda->id }}
                                @if(!empty($comanda->total))
                                    <span class="ml-1 text-xs" style="color: var(--rf-primary);">
                                        ${{ number_format($comanda->total, 0, ',', '.') }}
                                    </span>
                                @endif
                            @else
                                <span style="color: var(--rf-text-light);">—</span>
                            @endif
                        </span>
                    </div>

                    @if($comanda && ($comanda->items_count ?? 0) > 0)
                        <div class="mt-2 flex items-center gap-1 text-xs" style="color: var(--rf-text-light);">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>{{ $comanda->items_count }} {{ $comanda->items_count === 1 ? 'item' : 'items' }}</span>
                        </div>
                    @endif
                </button>
            @endforeach

            @if($mesas->isEmpty())
                <div class="flex-shrink-0 w-72 text-center py-8" style="color: var(--rf-text-light);">
                    No hay mesas disponibles
                </div>
            @endif
        </div>
    </div>

    @if($isMobile && $mesas->count() > 2)
        <div class="px-4 pb-3 flex justify-center gap-1">
            @foreach($mesas as $m)
                <div class="w-1 h-1 rounded-full transition-all"
                     style="background: {{ $mesaSelected && (int)$mesaSelected->id === (int)$m->id ? 'var(--rf-primary)' : 'var(--rf-border)' }};">
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
.overflow-x-auto {
    scrollbar-width: thin;
    -ms-overflow-style: -ms-autohiding-scrollbar;
}

.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: var(--rf-bg);
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: var(--rf-border);
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: var(--rf-text-light);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush