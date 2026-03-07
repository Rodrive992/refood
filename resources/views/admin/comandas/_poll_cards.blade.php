{{-- resources/views/admin/comandas/_poll_cards.blade.php --}}

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($comandas as $c)
        @php
            $cuentaPedida = (int)($c->cuenta_solicitada ?? 0) === 1;
            $estadoCaja = $c->estado_caja ?? null;

            /**
             * ✅ Mostrar TODOS los items (no preview)
             * Si querés optimizar (evitar N+1), lo ideal es eager load desde el controller:
             *   ->with(['items' => fn($q) => $q->select('id','comanda_id','nombre_snapshot','cantidad','nota')->orderBy('id')])
             * Por ahora lo dejamos acá para que funcione ya.
             */
            $allItems = $c->items()
                ->select(['id','nombre_snapshot','cantidad','nota'])
                ->orderBy('id', 'asc')
                ->get();

            $totalItems = (int)($c->items_count ?? $allItems->count());
        @endphp

        <div class="group rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden">
            {{-- Header clickable (abre CAJA) --}}
            <a href="{{ route('admin.caja.show', $c) }}" class="block">
                <div class="p-4 md:p-5 flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="text-base md:text-lg font-extrabold text-slate-900">
                                #{{ $c->id }}
                            </div>

                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                {{ $c->estado }}
                            </span>

                            @if($cuentaPedida)
                                <span class="text-xs font-extrabold px-2 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                    💳 Cuenta solicitada
                                </span>
                            @endif

                            @if(!empty($estadoCaja))
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                    Caja: {{ $estadoCaja }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 text-sm text-slate-600">
                            Mesa:
                            <span class="font-semibold text-slate-800">
                                {{ $c->mesa->nombre ?? 'Sin mesa' }}
                            </span>
                            · Mozo:
                            <span class="font-semibold text-slate-800">
                                {{ $c->mozo->name ?? '—' }}
                            </span>
                        </div>

                        @if($c->observacion)
                            <div class="mt-2 text-sm text-slate-700 line-clamp-2">
                                <span class="font-semibold">Obs:</span> {{ $c->observacion }}
                            </div>
                        @endif

                        {{-- ✅ Items completos --}}
                        @if($allItems->count() > 0)
                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <div class="text-xs font-bold text-slate-600 mb-1">
                                    Items solicitados ({{ $totalItems }})
                                </div>

                                <ul class="space-y-1">
                                    @foreach($allItems as $it)
                                        @php
                                            $qty = rtrim(rtrim(number_format((float)($it->cantidad ?? 0), 2, '.', ''), '0'), '.');
                                            $nombre = $it->nombre_snapshot ?? 'Item';
                                            $nota = trim((string)($it->nota ?? ''));
                                        @endphp

                                        <li class="text-sm text-slate-800 leading-snug">
                                            <span class="font-extrabold">{{ $qty }}</span>
                                            <span class="font-semibold">×</span>
                                            <span class="font-semibold">{{ $nombre }}</span>

                                            @if($nota !== '')
                                                <span class="text-xs text-slate-600 italic">
                                                    — “{{ $nota }}”
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="text-right shrink-0">
                        <div class="text-sm text-slate-600">Items</div>
                        <div class="text-lg font-extrabold text-slate-900">{{ (int)($c->items_count ?? $allItems->count()) }}</div>
                        <div class="text-xs text-slate-500 mt-1">
                            {{ \Carbon\Carbon::parse($c->opened_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m H:i') }}
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-5 pb-4 md:pb-5">
                    <div class="rounded-xl border px-3 py-2 text-sm transition
                        {{ $cuentaPedida
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-900 group-hover:bg-emerald-100'
                            : 'border-slate-200 bg-slate-50 text-slate-700 group-hover:bg-emerald-50 group-hover:border-emerald-200'
                        }}">
                        Abrir en caja → Imprimir cuenta / Cobrar
                    </div>
                </div>
            </a>

            {{-- Footer acciones (no navega a CAJA) --}}
            <div class="px-4 md:px-5 pb-4 flex items-center justify-between gap-2">
                {{-- Imprime directo (sin pestaña) --}}
                <a href="{{ route('admin.comandas.print', $c) }}"
                   class="js-print-comanda inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-extrabold
                          bg-white border border-slate-200 hover:bg-slate-50 text-slate-800"
                   data-print-url="{{ route('admin.comandas.print', $c) }}"
                   data-comanda-id="{{ (int)$c->id }}">
                    🖨️ Imprimir comanda
                </a>

                <a href="{{ route('admin.caja.show', $c) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-extrabold
                          bg-emerald-600 hover:bg-emerald-700 text-white">
                    Ir a CAJA →
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-6 text-slate-700">
            No hay comandas para los filtros seleccionados.
        </div>
    @endforelse
</div>