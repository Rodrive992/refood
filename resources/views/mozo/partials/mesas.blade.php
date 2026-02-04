@php
    $comandaActivaPorMesa = $comandasActivasPorMesa ?? collect();
@endphp

<div id="mesasPanel" data-ts="{{ now()->timestamp }}">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-3">
        @foreach ($mesas as $m)
            @php
                $tieneCuenta = false;

                if ($comandaActivaPorMesa instanceof \Illuminate\Support\Collection) {
                    $cMesa = $comandaActivaPorMesa->get($m->id);
                } else {
                    $cMesa = $comandaActivaPorMesa[$m->id] ?? null;
                }

                if ($cMesa) {
                    $tieneCuenta = (int)($cMesa->cuenta_solicitada ?? 0) === 1;
                }

                $estado = $m->estado;

                $badgeBg = 'bg-slate-100 text-slate-700';
                if ($tieneCuenta) {
                    $badgeBg = 'bg-emerald-100 text-emerald-800';
                } elseif ($estado === 'ocupada') {
                    $badgeBg = 'bg-amber-100 text-amber-800';
                } elseif ($estado === 'reservada') {
                    $badgeBg = 'bg-blue-100 text-blue-800';
                }

                $icon = '✅';
                if ($tieneCuenta) $icon = '💳';
                elseif ($estado === 'ocupada') $icon = '🍔';
                elseif ($estado === 'reservada') $icon = '🕒';

                $puedeVerReserva = ($estado === 'reservada' && !empty($m->observacion));
            @endphp

            <a href="{{ route('mozo.dashboard', ['mesa_id' => $m->id]) }}"
               class="rounded-2xl border p-3 shadow-sm hover:shadow-md transition border-slate-200 bg-white">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-extrabold text-slate-900 truncate">{{ $m->nombre }}</div>

                        <div class="mt-1 text-xs font-semibold px-2 py-1 rounded-full inline-flex {{ $badgeBg }}">
                            {{ $tieneCuenta ? 'cuenta' : $m->estado }}
                        </div>
                    </div>

                    <div class="text-xl">{{ $icon }}</div>
                </div>

                @if ($m->observacion)
                    <div class="mt-2 text-xs text-slate-600 line-clamp-2">
                        <span class="font-semibold">Obs:</span> {{ $m->observacion }}
                    </div>
                @endif

                {{-- ✅ Ver reserva en modal (solo si está reservada y tiene obs) --}}
                @if($puedeVerReserva)
                    <div class="mt-3">
                        <button type="button"
                                class="w-full rounded-lg px-3 py-2 text-xs font-extrabold border border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100"
                                onclick="event.preventDefault(); event.stopPropagation(); openReservaModal(@json($m->nombre), @json($m->observacion));">
                            Ver reserva
                        </button>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
</div>
