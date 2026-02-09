{{-- resources/views/mozo/partials/mesa.blade.php --}}
@php
    $isMobile = $isMobile ?? false;
@endphp

<div class="bg-white rounded-2xl border rf-scrollbar" style="border-color: var(--rf-border);">
    <div class="p-4 flex items-center justify-between">
        <div>
            <h2 class="font-bold">Mesas</h2>
            <p class="text-sm" style="color: var(--rf-text-light);">Tocá una mesa para abrir su comanda</p>
        </div>
        <button onclick="location.reload()"
            class="px-3 py-2 rounded-xl text-sm border rf-hover-lift"
            style="border-color: var(--rf-border);">
            Refrescar
        </button>
    </div>

    <div class="px-4 pb-4">
        <div class="{{ $isMobile ? 'grid grid-cols-2 gap-3' : 'grid grid-cols-3 gap-3' }}">
            @foreach($mesas as $m)
                @php
                    $isActive = $mesaSelected && (int)$mesaSelected->id === (int)$m->id;
                    $comanda = $comandasActivasPorMesa->get($m->id);
                    $estado = $m->estado;
                    $badge = match($estado){
                        'ocupada' => ['bg' => 'var(--rf-primary-soft)', 'tx' => 'var(--rf-primary-hover)', 'label' => 'Ocupada'],
                        'reservada' => ['bg' => 'rgba(59,130,246,0.12)', 'tx' => 'var(--rf-info)', 'label' => 'Reservada'],
                        'libre' => ['bg' => 'var(--rf-secondary-soft)', 'tx' => 'var(--rf-secondary-hover)', 'label' => 'Libre'],
                        default => ['bg' => 'rgba(107,114,128,0.12)', 'tx' => 'var(--rf-text-light)', 'label' => ucfirst($estado)],
                    };
                @endphp

                <button
                    type="button"
                    data-action="select-mesa"
                    data-mesa-id="{{ $m->id }}"
                    class="text-left rounded-2xl border p-3 rf-transition-smooth rf-hover-lift {{ $isActive ? 'ring-2 ring-orange-400' : '' }}"
                    style="border-color: var(--rf-border); background: {{ $isActive ? 'var(--rf-primary-soft)' : 'var(--rf-white)' }};"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-extrabold text-lg" style="color: var(--rf-text);">{{ $m->nombre }}</div>
                            <div class="mt-1 inline-flex items-center px-2 py-1 rounded-full text-xs"
                                style="background: {{ $badge['bg'] }}; color: {{ $badge['tx'] }};">
                                {{ $badge['label'] }}
                            </div>
                        </div>

                        @if($isActive)
                            <div class="text-xs font-bold px-2 py-1 rounded-full"
                                style="background: var(--rf-primary); color: white;">
                                Activa
                            </div>
                        @endif
                    </div>

                    @if(!empty($m->observacion))
                        <div class="mt-2 text-xs" style="color: var(--rf-text-light);">
                            {{ $m->observacion }}
                        </div>
                    @endif

                    <div class="mt-3 flex items-center justify-between text-xs" style="color: var(--rf-text-light);">
                        <span>Comanda</span>
                        <span class="font-semibold" style="color: var(--rf-text);">
                            {{ $comanda ? '#'.$comanda->id : '—' }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>
