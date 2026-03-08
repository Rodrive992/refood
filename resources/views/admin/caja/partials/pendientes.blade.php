{{-- resources/views/admin/caja/partials/pendientes.blade.php --}}

@php
  $count = $comandasPendientes->count();
@endphp

<div id="pendientesPanel" data-count="{{ $count }}" class="divide-y" style="border-color: #F1F5F9;">
    @forelse($comandasPendientes as $c)
        @php
            $mesaNombre = $c->mesa->nombre ?? 'Sin mesa';
            $mozoNombre = $c->mozo->name ?? ('Mozo #' . ($c->id_mozo ?? '-'));
            $totalEst = number_format((float)($c->total_estimado ?? 0), 0, ',', '.');
        @endphp

        <div class="p-3 flex items-start justify-between gap-3 hover:bg-slate-50 transition-colors">
            {{-- Info izquierda --}}
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                          style="background: #FEF3C7; color: #92400E;">
                        {{ $mesaNombre }}
                    </span>
                    <span class="text-[10px]" style="color: #64748B;">#{{ $c->id }}</span>
                </div>

                <div class="text-xs space-y-0.5" style="color: #475569;">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $mozoNombre }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ optional($c->cuenta_solicitada_at)->format('d/m H:i') }}
                        </span>
                    </div>
                    @if(!empty($c->cuenta_solicitada_nota))
                        <div class="text-[10px] italic" style="color: #64748B;">“{{ $c->cuenta_solicitada_nota }}”</div>
                    @endif
                </div>
            </div>

            {{-- Info derecha + acciones --}}
            <div class="flex items-center gap-2">
                <div class="text-right">
                    <div class="text-sm font-bold" style="color: #0F172A;">${{ $totalEst }}</div>
                    <div class="text-[9px]" style="color: #94A3B8;">estimado</div>
                </div>

                <div class="flex flex-col gap-1">
                    <a href="{{ route('admin.caja.cuenta', $c) }}"
                       class="js-print-preticket px-2.5 py-1 rounded-lg text-[15px] font-medium text-white text-center whitespace-nowrap"
                       style="background: #0F172A;"
                       data-print-url="{{ route('admin.caja.cuenta', $c) }}"
                       data-comanda-id="{{ (int)$c->id }}">
                        🖨️ Imprimir
                    </a>
                    <a href="{{ route('admin.caja.show', $c) }}"
                       class="px-2.5 py-1 rounded-lg text-[15px] font-medium text-center whitespace-nowrap"
                       style="background: white; border: 1px solid #E2E8F0; color: #475569;">
                        Cobrar
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full mb-3" style="background: #F1F5F9;">
                <svg class="w-6 h-6" style="color: #94A3B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium" style="color: #475569;">No hay cuentas pendientes</p>
            <p class="text-xs mt-1" style="color: #94A3B8;">Todo al día ✅</p>
        </div>
    @endforelse
</div>