<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Mesas</h2>
                <p class="text-sm text-gray-600">
                    Panel visual tipo POS · Local #{{ $idLocal }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Filtro estado --}}
                <form method="GET" action="{{ route('admin.mesas.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="id_local" value="{{ $idLocal }}">
                    <select name="estado"
                        class="rounded-xl border-gray-200 text-sm focus:border-orange-400 focus:ring-orange-400">
                        <option value="">Todos</option>
                        @foreach ($estados as $e)
                            <option value="{{ $e }}" @selected(($estado ?? '') === $e)>
                                {{ ucfirst(str_replace('_', ' ', $e)) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="inline-flex items-center rounded-xl bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filtrar
                    </button>

                    @if (!empty($estado))
                        <a href="{{ route('admin.mesas.index', ['id_local' => $idLocal]) }}"
                            class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Limpiar
                        </a>
                    @endif
                </form>

                {{-- Crear --}}
                <button
                    x-data
                    @click="$dispatch('open-modal', 'modal-crear-mesa')"
                    class="inline-flex items-center rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                    + Nueva mesa
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if (session('success'))
                <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <div class="font-semibold mb-1">Revisá estos errores:</div>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Stats rápidas --}}
            @php
                $countLibre = $mesas->where('estado', 'libre')->count();
                $countOcupada = $mesas->where('estado', 'ocupada')->count();
                $countReservada = $mesas->where('estado', 'reservada')->count();
                $countFuera = $mesas->where('estado', 'fuera_servicio')->count();
            @endphp

            <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">Libres</div>
                    <div class="text-2xl font-extrabold text-green-700">{{ $countLibre }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">Ocupadas</div>
                    <div class="text-2xl font-extrabold text-red-700">{{ $countOcupada }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">Reservadas</div>
                    <div class="text-2xl font-extrabold text-amber-700">{{ $countReservada }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">Fuera de servicio</div>
                    <div class="text-2xl font-extrabold text-gray-700">{{ $countFuera }}</div>
                </div>
            </div>

            {{-- GRILLA POS --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($mesas as $mesa)
                    @php
                        $estadoMesa = $mesa->estado;

                        $ring = 'ring-1 ring-gray-200';
                        $bg = 'bg-white';
                        $badgeBg = 'bg-gray-100';
                        $badgeText = 'text-gray-700';
                        $dot = 'bg-gray-400';

                        if ($estadoMesa === 'libre') {
                            $ring = 'ring-2 ring-green-200';
                            $bg = 'bg-green-50/60';
                            $badgeBg = 'bg-green-100';
                            $badgeText = 'text-green-800';
                            $dot = 'bg-green-600';
                        } elseif ($estadoMesa === 'ocupada') {
                            $ring = 'ring-2 ring-red-200';
                            $bg = 'bg-red-50/60';
                            $badgeBg = 'bg-red-100';
                            $badgeText = 'text-red-800';
                            $dot = 'bg-red-600';
                        } elseif ($estadoMesa === 'reservada') {
                            $ring = 'ring-2 ring-amber-200';
                            $bg = 'bg-amber-50/60';
                            $badgeBg = 'bg-amber-100';
                            $badgeText = 'text-amber-800';
                            $dot = 'bg-amber-600';
                        } elseif ($estadoMesa === 'fuera_servicio') {
                            $ring = 'ring-2 ring-gray-200';
                            $bg = 'bg-gray-50';
                            $badgeBg = 'bg-gray-200';
                            $badgeText = 'text-gray-800';
                            $dot = 'bg-gray-600';
                        }
                    @endphp

                    <article class="rounded-3xl {{ $bg }} {{ $ring }} overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dot }}"></span>
                                        <h3 class="text-lg font-extrabold text-gray-900 leading-tight">
                                            {{ $mesa->nombre }}
                                        </h3>
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-xl px-2.5 py-1 text-xs font-bold {{ $badgeBg }} {{ $badgeText }}">
                                            {{ ucfirst(str_replace('_', ' ', $mesa->estado)) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-xl bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                            Cap: {{ $mesa->capacidad }}
                                        </span>
                                        <span class="inline-flex items-center rounded-xl bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                            ID: {{ $mesa->id }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Botón Edit --}}
                                <button
                                    x-data
                                    @click="$dispatch('open-edit-mesa', {
                                        id: {{ $mesa->id }},
                                        id_local: {{ $mesa->id_local }},
                                        nombre: @js($mesa->nombre),
                                        capacidad: {{ (int) $mesa->capacidad }},
                                        estado: @js($mesa->estado),
                                        observacion: @js($mesa->observacion)
                                    })"
                                    class="rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                    Editar
                                </button>
                            </div>

                            {{-- Observación --}}
                            <div class="mt-3 min-h-[44px] rounded-2xl bg-white/70 border border-gray-200 px-3 py-2">
                                <div class="text-xs font-semibold text-gray-500 mb-0.5">Observación</div>
                                <div class="text-sm text-gray-800 line-clamp-2">
                                    {{ $mesa->observacion ?: '—' }}
                                </div>
                            </div>

                            {{-- Acciones rápidas --}}
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                {{-- Reservar (modal) --}}
                                <button
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-reservar-mesa', {
                                        id: {{ $mesa->id }},
                                        nombre: @js($mesa->nombre),
                                        observacion: @js($mesa->observacion)
                                    })"
                                    class="w-full rounded-2xl bg-amber-500 px-3 py-2 text-sm font-bold text-white hover:bg-amber-600"
                                >
                                    Reservar
                                </button>

                                {{-- Liberar --}}
                                <form method="POST" action="{{ route('admin.mesas.liberar', $mesa) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="w-full rounded-2xl bg-green-600 px-3 py-2 text-sm font-bold text-white hover:bg-green-700">
                                        Liberar
                                    </button>
                                </form>
                            </div>

                            {{-- Zona de peligro (compacta) --}}
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}"
                                    onsubmit="return confirm('¿Eliminar {{ $mesa->nombre }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-semibold text-red-700 hover:text-red-800">
                                        Eliminar
                                    </button>
                                </form>

                                <span class="text-xs text-gray-500">
                                    Actualizada: {{ optional($mesa->updated_at)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-gray-200 bg-white p-8 text-center">
                        <div class="text-lg font-bold text-gray-900">No hay mesas</div>
                        <p class="mt-1 text-sm text-gray-600">Creá la primera mesa para este local.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- =========================
        MODAL: RESERVAR (RÁPIDO + OBS)
    ========================== --}}
    <div
        x-data="{
            open: false,
            mesaId: null,
            mesaNombre: '',
            observacion: '',
            actionUrl: '',
            setAction(id){
                this.actionUrl = '{{ route('admin.mesas.estado', ['mesa' => '___ID___']) }}'.replace('___ID___', id);
            },
            focusObs(){
                this.$nextTick(() => {
                    const el = this.$refs.obsRes;
                    if (el) el.focus();
                });
            }
        }"
        x-on:open-reservar-mesa.window="
            open = true;
            mesaId = $event.detail.id;
            mesaNombre = $event.detail.nombre ?? '';
            observacion = ($event.detail.observacion ?? '');
            setAction(mesaId);
            focusObs();
        "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
    >
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900">Reservar mesa</h3>
                    <p class="text-sm text-gray-600" x-text="mesaNombre + ' · ID #' + mesaId"></p>
                </div>
                <button @click="open=false"
                    class="rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold hover:bg-gray-50">
                    Cerrar
                </button>
            </div>

            <form method="POST" :action="actionUrl" class="p-5 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="estado" value="reservada">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Observación (ej: “20:30 - Cumple de Ana”)
                    </label>
                    <input
                        x-ref="obsRes"
                        type="text"
                        name="observacion"
                        x-model="observacion"
                        placeholder="20:30 - Cumple de Ana"
                        class="w-full rounded-2xl border-gray-200 focus:border-amber-400 focus:ring-amber-400"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Tip: podés poner hora, nombre, cantidad o detalle rápido.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="open=false"
                        class="rounded-2xl border border-gray-200 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="rounded-2xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600">
                        Confirmar reservada
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================
        MODAL: CREAR MESA
    ========================== --}}
    <div
        x-data="{ open: false }"
        x-on:open-modal.window="if ($event.detail === 'modal-crear-mesa') open = true"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
    >
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900">Nueva mesa</h3>
                    <p class="text-sm text-gray-600">Se crea en el local #{{ $idLocal }}</p>
                </div>
                <button @click="open=false" class="rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold hover:bg-gray-50">
                    Cerrar
                </button>
            </div>

            <form method="POST" action="{{ route('admin.mesas.store') }}" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="id_local" value="{{ $idLocal }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}"
                            placeholder="Mesa 11"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Capacidad</label>
                        <input type="number" name="capacidad" min="1" max="50" value="{{ old('capacidad', 4) }}"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
                        <select name="estado" class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                            @foreach ($estados as $e)
                                <option value="{{ $e }}" @selected(old('estado', 'libre') === $e)>
                                    {{ ucfirst(str_replace('_', ' ', $e)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Observación</label>
                        <input type="text" name="observacion" value="{{ old('observacion') }}"
                            placeholder="Opcional"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="open=false"
                        class="rounded-2xl border border-gray-200 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="rounded-2xl bg-orange-600 px-4 py-2 text-sm font-bold text-white hover:bg-orange-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================
        MODAL: EDITAR MESA
    ========================== --}}
    <div
        x-data="{
            open: false,
            mesaId: null,
            nombre: '',
            capacidad: 4,
            estado: 'libre',
            observacion: '',
            actionUrl: '',
            setAction(id){
                this.actionUrl = '{{ route('admin.mesas.update', ['mesa' => '___ID___']) }}'.replace('___ID___', id);
            }
        }"
        x-on:open-edit-mesa.window="
            open = true;
            mesaId = $event.detail.id;
            nombre = $event.detail.nombre ?? '';
            capacidad = $event.detail.capacidad ?? 4;
            estado = $event.detail.estado ?? 'libre';
            observacion = $event.detail.observacion ?? '';
            setAction(mesaId);
        "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
    >
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900">Editar mesa</h3>
                    <p class="text-sm text-gray-600" x-text="'ID #' + mesaId"></p>
                </div>
                <button @click="open=false" class="rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold hover:bg-gray-50">
                    Cerrar
                </button>
            </div>

            <form method="POST" :action="actionUrl" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" x-model="nombre"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Capacidad</label>
                        <input type="number" name="capacidad" min="1" max="50" x-model="capacidad"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
                        <select name="estado" x-model="estado"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400">
                            @foreach ($estados as $e)
                                <option value="{{ $e }}">{{ ucfirst(str_replace('_', ' ', $e)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Observación</label>
                        <input type="text" name="observacion" x-model="observacion"
                            class="w-full rounded-2xl border-gray-200 focus:border-orange-400 focus:ring-orange-400"
                            placeholder="Opcional">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-2">
                    <button type="button" @click="open=false"
                        class="rounded-2xl border border-gray-200 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="rounded-2xl bg-orange-600 px-4 py-2 text-sm font-bold text-white hover:bg-orange-700">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
