@php
  // Para detectar cambios en el front
  $count = $comandasPendientes->count();
@endphp

<div id="pendientesPanel" data-count="{{ $count }}" class="divide-y divide-gray-100">
    @forelse($comandasPendientes as $c)
        @php
            $mesaNombre = $c->mesa->nombre ?? 'Sin mesa';
            $mozoNombre = $c->mozo->name ?? ('Mozo #' . ($c->id_mozo ?? '-'));
            $totalEst = number_format((float)($c->total_estimado ?? 0), 0, ',', '.');
        @endphp

        <div class="p-4 md:p-5 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                        {{ $mesaNombre }}
                    </span>
                    <span class="text-sm text-gray-500">Comanda #{{ $c->id }}</span>
                </div>

                <div class="mt-2 text-sm text-gray-700">
                    <div><span class="font-semibold">Mozo:</span> {{ $mozoNombre }}</div>
                    <div><span class="font-semibold">Solicitada:</span> {{ optional($c->cuenta_solicitada_at)->format('d/m H:i') }}</div>

                    @if(!empty($c->cuenta_solicitada_nota))
                        <div class="mt-1 text-gray-600 italic">“{{ $c->cuenta_solicitada_nota }}”</div>
                    @endif
                </div>
            </div>

            <div class="shrink-0 text-right">
                <div class="text-xl font-extrabold text-gray-900">$ {{ $totalEst }}</div>
                <div class="text-xs text-gray-500 mt-1">estimado</div>

                <div class="mt-3 flex flex-col sm:flex-row gap-2 justify-end">
                    <a href="{{ route('admin.caja.cuenta', $c) }}"
                       class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:opacity-90">
                        Imprimir cuenta
                    </a>

                    <a href="{{ route('admin.caja.show', $c) }}"
                       class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-semibold">
                        Cobrar
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="p-6 text-sm text-gray-600">
            No hay cuentas pendientes. ✅
        </div>
    @endforelse
</div>
