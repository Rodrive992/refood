{{-- resources/views/admin/caja/partials/mesas.blade.php --}}

<div id="mesasPanel" class="p-3 grid grid-cols-2 md:grid-cols-3 gap-2">
    @foreach($mesas as $m)
        @php
            $estado = $m->estado ?? 'libre';
            $pendiente = $pendientesPorMesa->get($m->id);

            $bg = 'bg-white';
            $badgeColor = '#F1F5F9';
            $badgeText = '#334155';

            if ($estado === 'ocupada') {
                $bg = '#FFF7ED';
                $badgeColor = '#FFEDD5';
                $badgeText = '#C2410C';
            } elseif ($estado === 'reservada') {
                $bg = '#EFF6FF';
                $badgeColor = '#DBEAFE';
                $badgeText = '#1E40AF';
            } elseif ($estado === 'libre') {
                $bg = '#F0FDF4';
                $badgeColor = '#DCFCE7';
                $badgeText = '#166534';
            }
        @endphp

        <div class="relative group rounded-xl p-2.5 transition-all hover:shadow-md" 
             style="background: {{ $bg }}; border: 1px solid #E2E8F0;">
            
            {{-- Cabecera --}}
            <div class="flex items-start justify-between gap-1 mb-1.5">
                <span class="font-bold text-xm truncate" style="color: #0F172A;">{{ $m->nombre }}</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" 
                      style="background: {{ $badgeColor }}; color: {{ $badgeText }};">
                    {{ strtoupper($estado) }}
                </span>
            </div>

            {{-- Info rápida --}}
            @if($pendiente)
                <div class="text-[10px]" style="color: #475569;">
                    <div>Comanda #{{ $pendiente->id }}</div>
                    <div class="truncate">{{ $pendiente->mozo->name ?? 'Mozo' }}</div>
                </div>
            @elseif(!empty($m->observacion))
                <div class="text-[10px] truncate italic" style="color: #94A3B8;">
                    {{ $m->observacion }}
                </div>
            @else
                <div class="text-[10px]" style="color: #94A3B8;">Sin novedades</div>
            @endif

            {{-- Tooltip --}}
            <div class="absolute z-30 hidden group-hover:block left-1/2 -translate-x-1/2 bottom-full mb-2 w-56 rounded-lg p-2"
                 style="background: #0F172A; color: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                <div class="text-xs font-bold mb-1.5">{{ $m->nombre }}</div>
                <div class="space-y-1 text-[10px] opacity-90">
                    <div><span class="font-medium">Estado:</span> {{ $estado }}</div>
                    @if($pendiente)
                        <div><span class="font-medium">Comanda:</span> #{{ $pendiente->id }}</div>
                        <div><span class="font-medium">Mozo:</span> {{ $pendiente->mozo->name ?? '—' }}</div>
                        <div><span class="font-medium">Solicitada:</span> {{ optional($pendiente->cuenta_solicitada_at)->format('d/m H:i') }}</div>
                        @if(!empty($pendiente->cuenta_solicitada_nota))
                            <div><span class="font-medium">Nota:</span> {{ $pendiente->cuenta_solicitada_nota }}</div>
                        @endif
                        <div><span class="font-medium">Total:</span> ${{ number_format((float)($pendiente->total_estimado ?? 0), 0, ',', '.') }}</div>
                    @elseif(!empty($m->observacion))
                        <div><span class="font-medium">Obs:</span> {{ $m->observacion }}</div>
                    @endif
                </div>
                <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px]" style="border-top-color: #0F172A;"></div>
            </div>
        </div>
    @endforeach
</div>