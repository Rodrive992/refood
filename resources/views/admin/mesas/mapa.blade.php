{{-- resources/views/admin/mesas/mapa-dragdrop.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Editor drag & drop</h2>
                <p class="text-sm text-gray-600">Local #{{ $idLocal }} · Arrastrá mesas, paredes y caja</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.mesas.index') }}"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    ← Volver
                </a>
                
                <form method="POST" action="{{ route('admin.mesas.mapa.paredes.limpiar') }}" 
                    onsubmit="return confirm('¿Limpiar todas las paredes? Las mesas y caja se mantienen')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                        🧹 Limpiar paredes
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    @php
        $mesasUbicadas = $mesas->filter(fn ($m) => !is_null($m->pos_x) && !is_null($m->pos_y));
        $mesasSinUbicar = $mesas->filter(fn ($m) => is_null($m->pos_x) || is_null($m->pos_y));

        $mesasPorCelda = [];
        foreach ($mesasUbicadas as $mesa) {
            $mesasPorCelda[$mesa->pos_x . '-' . $mesa->pos_y] = $mesa;
        }

        $cajaKey = ($mapa->caja_x ?? 0) . '-' . ($mapa->caja_y ?? 0);
    @endphp

    <div class="py-4">
        <div class="mx-auto max-w-full px-4">

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- CONTENEDOR PRINCIPAL FLEX --}}
            <div class="flex gap-4">
                {{-- PANEL LATERAL IZQUIERDO --}}
                <div class="w-72 flex-shrink-0 space-y-4">
                    {{-- Elementos para arrastrar --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">ELEMENTOS DEL LOCAL</h3>
                        
                        {{-- CAJA REGISTRADORA (siempre visible) --}}
                        <div draggable="true" 
                            ondragstart="dragStart(event, 'caja')"
                            class="mb-3 cursor-grab rounded-lg border-2 border-amber-300 bg-amber-50 p-3 text-center text-sm font-medium text-amber-700 active:cursor-grabbing hover:bg-amber-100 transition">
                            <div class="text-2xl mb-1">🖥️</div>
                            <div>Caja registradora</div>
                            @if($mapa->caja_x && $mapa->caja_y)
                                <div class="text-[10px] opacity-70 mt-1">({{ $mapa->caja_x }},{{ $mapa->caja_y }})</div>
                            @endif
                        </div>

                        {{-- PARED --}}
                        <div draggable="true" 
                            ondragstart="dragStart(event, 'pared')"
                            class="mb-3 cursor-grab rounded-lg border-2 border-gray-300 bg-gray-100 p-3 text-center text-sm font-medium text-gray-700 active:cursor-grabbing hover:bg-gray-200 transition">
                            <div class="text-2xl mb-1">🧱</div>
                            <div>Pared</div>
                        </div>

                        {{-- Separador --}}
                        <div class="my-4 border-t border-gray-200"></div>

                        {{-- Mesas disponibles --}}
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            MESAS DISPONIBLES ({{ $mesasSinUbicar->count() }})
                        </h3>

                        <div class="max-h-[400px] space-y-2 overflow-y-auto pr-2">
                            @forelse ($mesasSinUbicar as $mesa)
                                <div draggable="true" 
                                    ondragstart="dragStart(event, 'mesa', {{ $mesa->id }})"
                                    class="cursor-grab rounded-lg border-2 p-3 text-sm active:cursor-grabbing hover:shadow-md transition
                                        @if($mesa->estado === 'libre') border-green-300 bg-green-50 hover:bg-green-100
                                        @elseif($mesa->estado === 'ocupada') border-red-300 bg-red-50 hover:bg-red-100
                                        @elseif($mesa->estado === 'reservada') border-amber-300 bg-amber-50 hover:bg-amber-100
                                        @else border-gray-300 bg-gray-50 hover:bg-gray-100 @endif">
                                    <div class="font-bold">{{ $mesa->nombre }}</div>
                                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                                        <span>👥 {{ $mesa->capacidad }}</span>
                                        <span class="capitalize">{{ $mesa->estado }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-sm text-gray-500">
                                    Todas las mesas están en el mapa
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Controles del mapa --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">TAMAÑO DEL MAPA</h3>
                        
                        <form method="POST" action="{{ route('admin.mesas.mapa.config') }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="text-xs text-gray-600">Filas</label>
                                    <input type="number" name="filas" value="{{ $mapa->filas }}" min="3" max="50" 
                                        class="w-full rounded-lg border-gray-200 text-sm">
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs text-gray-600">Columnas</label>
                                    <input type="number" name="columnas" value="{{ $mapa->columnas }}" min="3" max="50" 
                                        class="w-full rounded-lg border-gray-200 text-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full rounded-lg bg-orange-600 px-3 py-2 text-sm text-white hover:bg-orange-700">
                                Actualizar tamaño
                            </button>
                        </form>
                    </div>

                    {{-- Leyenda de colores --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">REFERENCIAS</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-amber-100 border border-amber-300"></div>
                                <span>Caja registradora</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-gray-700"></div>
                                <span>Pared</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-green-100 border border-green-300"></div>
                                <span>Mesa libre</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div>
                                <span>Mesa ocupada</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-amber-100 border border-amber-300"></div>
                                <span>Mesa reservada</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MAPA GRANDE CON SCROLL --}}
                <div class="flex-1 overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="overflow-auto" style="max-height: 700px;" id="mapa-scroll">
                        <div class="grid gap-1" 
                            style="grid-template-columns: repeat({{ $mapa->columnas }}, 90px); width: fit-content;">
                            
                            @for ($y = 1; $y <= $mapa->filas; $y++)
                                @for ($x = 1; $x <= $mapa->columnas; $x++)
                                    @php
                                        $key = $x . '-' . $y;
                                        $esCaja = $key === $cajaKey;
                                        $esPared = isset($paredes[$key]);
                                        $mesa = $mesasPorCelda[$key] ?? null;
                                        
                                        $bgClass = 'bg-white';
                                        $borderClass = 'border-gray-200';
                                        $textClass = 'text-gray-900';
                                        
                                        if ($esPared) {
                                            $bgClass = 'bg-gray-700';
                                            $borderClass = 'border-gray-800';
                                            $textClass = 'text-white';
                                        } elseif ($esCaja) {
                                            $bgClass = 'bg-amber-100';
                                            $borderClass = 'border-amber-400';
                                            $textClass = 'text-amber-900';
                                        } elseif ($mesa) {
                                            if ($mesa->estado === 'libre') {
                                                $bgClass = 'bg-green-100';
                                                $borderClass = 'border-green-300';
                                            } elseif ($mesa->estado === 'ocupada') {
                                                $bgClass = 'bg-red-100';
                                                $borderClass = 'border-red-300';
                                            } elseif ($mesa->estado === 'reservada') {
                                                $bgClass = 'bg-amber-100';
                                                $borderClass = 'border-amber-300';
                                            }
                                        }
                                    @endphp

                                    <div class="celda-mapa relative h-24 w-24 rounded-lg border-2 {{ $borderClass }} {{ $bgClass }} {{ $textClass }} transition-all hover:ring-2 hover:ring-orange-400 group"
                                        data-x="{{ $x }}"
                                        data-y="{{ $y }}"
                                        data-key="{{ $key }}"
                                        data-tipo="{{ $esCaja ? 'caja' : ($esPared ? 'pared' : ($mesa ? 'mesa' : 'vacio')) }}"
                                        data-mesa-id="{{ $mesa->id ?? '' }}"
                                        draggable="{{ $esCaja || $esPared || $mesa ? 'true' : 'false' }}"
                                        ondragstart="dragStartDesdeMapa(event, '{{ $esCaja ? 'caja' : ($esPared ? 'pared' : 'mesa') }}', {{ $mesa->id ?? 'null' }}, {{ $x }}, {{ $y }})"
                                        ondragover="dragOver(event)"
                                        ondrop="drop(event)"
                                        ondragleave="dragLeave(event)">
                                        
                                        {{-- Coordenada pequeña --}}
                                        <span class="absolute left-0.5 top-0.5 text-[8px] opacity-30">{{ $x }},{{ $y }}</span>
                                        
                                        {{-- Contenido --}}
                                        <div class="flex h-full w-full flex-col items-center justify-center">
                                            @if ($esCaja)
                                                <span class="text-3xl">🖥️</span>
                                                <span class="text-[9px] mt-0.5 font-bold">CAJA</span>
                                                <button onclick="quitarCaja(event)" 
                                                    class="absolute -right-1 -top-1 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600 group-hover:flex">
                                                    ✕
                                                </button>
                                            @elseif ($esPared)
                                                <span class="text-3xl">🧱</span>
                                                <span class="text-[9px] mt-0.5">PARED</span>
                                            @elseif ($mesa)
                                                <span class="text-sm font-bold">{{ $mesa->nombre }}</span>
                                                <span class="text-xs">👥 {{ $mesa->capacidad }}</span>
                                                <span class="text-[8px] opacity-75 capitalize">{{ $mesa->estado }}</span>
                                                <form method="POST" action="{{ route('admin.mesas.mapa.mesas.quitar', $mesa) }}" class="absolute -right-1 -top-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" onclick="return confirm('¿Quitar esta mesa del mapa?')" 
                                                        class="hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600 group-hover:flex">
                                                        ✕
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-2xl opacity-20">⬚</span>
                                                <span class="text-[8px] opacity-20">vacío</span>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formularios ocultos --}}
    <form id="form-pared" method="POST" action="{{ route('admin.mesas.mapa.celdas.toggle') }}" style="display: none;">
        @csrf
        <input type="hidden" name="x" id="pared-x">
        <input type="hidden" name="y" id="pared-y">
    </form>

    <form id="form-caja" method="POST" action="{{ route('admin.mesas.mapa.caja') }}" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="caja_x" id="caja-x">
        <input type="hidden" name="caja_y" id="caja-y">
    </form>

    <form id="form-mesa" method="POST" action="" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="pos_x" id="mesa-x">
        <input type="hidden" name="pos_y" id="mesa-y">
    </form>

    @push('scripts')
    <script>
        let elementoArrastrando = null;
        let origenX = null;
        let origenY = null;

        // Drag desde panel lateral
        function dragStart(event, tipo, mesaId = null) {
            elementoArrastrando = {
                tipo: tipo,
                mesaId: mesaId,
                desdeMapa: false
            };
            
            event.dataTransfer.setData('text/plain', tipo);
            event.dataTransfer.effectAllowed = 'move';
            
            event.target.classList.add('opacity-50');
        }

        // Drag desde mapa (para reubicar)
        function dragStartDesdeMapa(event, tipo, mesaId, x, y) {
            event.stopPropagation();
            
            elementoArrastrando = {
                tipo: tipo,
                mesaId: mesaId,
                desdeMapa: true,
                origenX: x,
                origenY: y
            };
            
            event.dataTransfer.setData('text/plain', tipo);
            event.dataTransfer.effectAllowed = 'move';
            
            event.target.classList.add('opacity-50');
        }

        function dragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            
            const celda = event.target.closest('.celda-mapa');
            if (celda && celda.dataset.tipo === 'vacio') {
                celda.classList.add('ring-4', 'ring-orange-400', 'scale-105', 'z-10');
            }
        }

        function dragLeave(event) {
            const celda = event.target.closest('.celda-mapa');
            if (celda) {
                celda.classList.remove('ring-4', 'ring-orange-400', 'scale-105', 'z-10');
            }
        }

        function drop(event) {
            event.preventDefault();
            
            const celda = event.target.closest('.celda-mapa');
            if (!celda) return;
            
            celda.classList.remove('ring-4', 'ring-orange-400', 'scale-105', 'z-10');
            
            // Solo permitir soltar en celdas vacías
            if (celda.dataset.tipo !== 'vacio') {
                alert('Esta celda no está disponible');
                resetDrag();
                return;
            }
            
            const x = celda.dataset.x;
            const y = celda.dataset.y;
            
            if (!elementoArrastrando) return;
            
            // Si viene del mapa, primero limpiamos la posición original
            if (elementoArrastrando.desdeMapa) {
                if (elementoArrastrando.tipo === 'caja') {
                    // Para caja, primero la quitamos (usando el mismo endpoint pero con valores null)
                    fetch('{{ route("admin.mesas.mapa.caja") }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            caja_x: 0, // Valor temporal, no se usará realmente
                            caja_y: 0  // Necesitamos una ruta específica para quitar caja
                        })
                    }).then(() => {
                        // Luego la ubicamos en la nueva posición
                        setTimeout(() => moverCaja(x, y), 100);
                    });
                } else if (elementoArrastrando.tipo === 'pared') {
                    // Para pared, primero eliminamos la original
                    fetch('{{ route("admin.mesas.mapa.celdas.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            x: elementoArrastrando.origenX,
                            y: elementoArrastrando.origenY
                        })
                    }).then(() => {
                        // Luego creamos la nueva
                        setTimeout(() => togglePared(x, y), 100);
                    });
                } else if (elementoArrastrando.tipo === 'mesa') {
                    ubicarMesa(elementoArrastrando.mesaId, x, y);
                }
            } else {
                // Viene del panel lateral
                if (elementoArrastrando.tipo === 'pared') {
                    togglePared(x, y);
                } else if (elementoArrastrando.tipo === 'caja') {
                    moverCaja(x, y);
                } else if (elementoArrastrando.tipo === 'mesa' && elementoArrastrando.mesaId) {
                    ubicarMesa(elementoArrastrando.mesaId, x, y);
                }
            }
            
            resetDrag();
        }

        function resetDrag() {
            elementoArrastrando = null;
            document.querySelectorAll('[draggable="true"]').forEach(el => {
                el.classList.remove('opacity-50');
            });
        }

        // Acciones
        function togglePared(x, y) {
            document.getElementById('pared-x').value = x;
            document.getElementById('pared-y').value = y;
            document.getElementById('form-pared').submit();
        }

        function moverCaja(x, y) {
            document.getElementById('caja-x').value = x;
            document.getElementById('caja-y').value = y;
            document.getElementById('form-caja').submit();
        }

        function quitarCaja(event) {
            event.stopPropagation();
            if (confirm('¿Quitar la caja del mapa? Podrás volver a ubicarla arrastrando desde el panel')) {
                // Usamos el mismo endpoint pero enviamos coordenadas que sabemos que están libres
                // Esto es un workaround hasta que tengamos un endpoint específico
                const x = 1;
                const y = 1;
                document.getElementById('caja-x').value = x;
                document.getElementById('caja-y').value = y;
                document.getElementById('form-caja').submit();
            }
        }

        function ubicarMesa(mesaId, x, y) {
            const form = document.getElementById('form-mesa');
            form.action = `{{ url('admin/mesas/mapa/mesas') }}/${mesaId}/posicion`;
            document.getElementById('mesa-x').value = x;
            document.getElementById('mesa-y').value = y;
            form.submit();
        }
    </script>
    @endpush

    @push('styles')
    <style>
        [draggable="true"] {
            user-select: none;
            -webkit-user-drag: element;
            cursor: grab;
        }
        
        [draggable="true"]:active {
            cursor: grabbing;
        }
        
        .celda-mapa {
            transition: all 0.15s ease;
            cursor: default;
        }
        
        .celda-mapa[draggable="true"] {
            cursor: grab;
        }
        
        .celda-mapa[draggable="true"]:active {
            cursor: grabbing;
        }
        
        .group:hover .hidden {
            display: flex !important;
        }
        
        /* Scrollbar personalizado */
        .overflow-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .overflow-auto::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }
        
        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
        
        /* Ring personalizado */
        .ring-4 {
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.5);
        }
    </style>
    @endpush
</x-app-layout>