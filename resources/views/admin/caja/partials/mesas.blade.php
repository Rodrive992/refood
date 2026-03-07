{{-- resources/views/admin/caja/partials/mesas.blade.php --}}

<div id="mesasPanel" class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3">
    @foreach($mesas as $m)
        @php
            $estado = $m->estado ?? 'libre';
            $pendiente = $pendientesPorMesa->get($m->id);

            $bg = 'bg-white';
            $badge = 'bg-slate-100 text-slate-700';

            if ($estado === 'ocupada') {
                $bg = 'bg-orange-50';
                $badge = 'bg-orange-100 text-orange-800';
            } elseif ($estado === 'reservada') {
                $bg = 'bg-blue-50';
                $badge = 'bg-blue-100 text-blue-800';
            } elseif ($estado === 'libre') {
                $bg = 'bg-emerald-50';
                $badge = 'bg-emerald-100 text-emerald-800';
            }

            $tooltipMesa = [];
            $tooltipMesa[] = 'Mesa: ' . ($m->nombre ?? '—');
            $tooltipMesa[] = 'Estado: ' . ($estado ?: '—');

            if ($pendiente) {
                $tooltipMesa[] = 'Comanda: #' . $pendiente->id;
                $tooltipMesa[] = 'Mozo: ' . ($pendiente->mozo->name ?? ('Mozo #' . ($pendiente->id_mozo ?? '-')));
                $tooltipMesa[] = 'Solicitada: ' . (optional($pendiente->cuenta_solicitada_at)->format('d/m H:i') ?: '—');

                if (!empty($pendiente->cuenta_solicitada_nota)) {
                    $tooltipMesa[] = 'Nota: ' . $pendiente->cuenta_solicitada_nota;
                }
            } else {
                if (!empty($m->observacion)) {
                    $tooltipMesa[] = 'Obs: ' . $m->observacion;
                }
            }

            $tooltipText = implode(' | ', $tooltipMesa);
        @endphp

        <div class="rounded-2xl border border-slate-200 p-3 {{ $bg }}"
             title="{{ $tooltipText }}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="font-extrabold text-slate-900 truncate">{{ $m->nombre }}</div>

                    <div class="text-xs text-slate-600 mt-1">
                        Estado: <span class="font-bold">{{ $estado }}</span>
                    </div>

                    @if($pendiente)
                        <div class="mt-2 text-xs text-slate-700">
                            <div><span class="font-semibold">Comanda:</span> #{{ $pendiente->id }}</div>
                            <div><span class="font-semibold">Mozo:</span> {{ $pendiente->mozo->name ?? ('Mozo #' . ($pendiente->id_mozo ?? '-')) }}</div>
                        </div>
                    @elseif(!empty($m->observacion))
                        <div class="text-xs text-slate-500 mt-2 truncate">
                            {{ $m->observacion }}
                        </div>
                    @endif
                </div>

                <div class="shrink-0 text-xs font-extrabold px-2 py-1 rounded-full {{ $badge }}">
                    {{ strtoupper($estado) }}
                </div>
            </div>
        </div>
    @endforeach
</div>