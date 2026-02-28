@extends('layouts.app')

@section('title', 'Caja · Mozos')

@section('content')
@php
    $q = $q ?? request('q', '');
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold" style="color: var(--rf-text);">
                Caja · Mozos
            </h1>
            <p class="text-sm mt-1" style="color: var(--rf-text-light);">
                Activá/Inactivá mozos y editá el nombre visible para comandas.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.caja.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-semibold hover:bg-gray-50"
               style="border-color: var(--rf-border); color: var(--rf-text);">
                <span>←</span> Volver a Caja
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl border text-sm"
             style="border-color: rgba(22,163,74,.35); background: rgba(22,163,74,.08); color: var(--rf-text);">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl border text-sm"
             style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--rf-text);">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl border text-sm"
             style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--rf-text);">
            <div class="font-bold mb-1">Revisá esto:</div>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Buscador --}}
    <div class="bg-white rounded-2xl border p-4 mb-4"
         style="border-color: var(--rf-border);">
        <form method="GET" action="{{ route('admin.caja.mozos.index') }}" class="flex flex-col md:flex-row gap-3 md:items-end">
            <div class="flex-1">
                <label class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                    Buscar
                </label>
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Nombre o email..."
                       class="mt-1 w-full rounded-xl border px-3 py-2 text-sm outline-none"
                       style="border-color: var(--rf-border); color: var(--rf-text); background: var(--rf-white);">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-bold"
                        style="background: var(--rf-green); color: white;">
                    Buscar
                </button>

                <a href="{{ route('admin.caja.mozos.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-bold border hover:bg-gray-50"
                   style="border-color: var(--rf-border); color: var(--rf-text);">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border overflow-hidden"
         style="border-color: var(--rf-border);">

        <div class="px-4 py-3 border-b flex items-center justify-between"
             style="border-color: var(--rf-border);">
            <div class="text-sm font-bold" style="color: var(--rf-text);">
                Mozos ({{ is_countable($mozos ?? []) ? count($mozos) : ($mozos->count() ?? 0) }})
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 font-extrabold" style="color: var(--rf-text);">Mozo</th>
                        <th class="text-left px-4 py-3 font-extrabold" style="color: var(--rf-text);">Email</th>
                        <th class="text-left px-4 py-3 font-extrabold" style="color: var(--rf-text);">Estado</th>
                        <th class="text-right px-4 py-3 font-extrabold" style="color: var(--rf-text);">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y" style="border-color: rgba(0,0,0,.06);">
                @forelse(($mozos ?? []) as $m)
                    @php
                        $estado = (string) ($m->estado ?? 'activo'); // 'activo'|'inactivo'
                        $activo = ($estado === 'activo');
                        $displayName = $m->name ?? 'Sin nombre';
                        $email = $m->email ?? '-';
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-bold" style="color: var(--rf-text);">
                                {{ $displayName }}
                            </div>
                            <div class="text-xs" style="color: var(--rf-text-light);">
                                ID #{{ $m->id }}
                            </div>
                        </td>

                        <td class="px-4 py-3" style="color: var(--rf-text);">
                            {{ $email }}
                        </td>

                        <td class="px-4 py-3">
                            @if($activo)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold"
                                      style="background: rgba(22,163,74,.12); color: rgb(22,163,74);">
                                    ACTIVO
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold"
                                      style="background: rgba(239,68,68,.12); color: rgb(239,68,68);">
                                    INACTIVO
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">

                                {{-- Editar nombre --}}
                                <button type="button"
                                        class="px-3 py-2 rounded-xl text-xs font-extrabold border hover:bg-gray-50"
                                        style="border-color: var(--rf-border); color: var(--rf-text);"
                                        data-open-edit
                                        data-user-id="{{ $m->id }}"
                                        data-user-name="{{ e($displayName) }}">
                                    Editar nombre
                                </button>

                                {{-- Toggle estado (no manda estado, el controller hace toggle) --}}
                                <form method="POST" action="{{ route('admin.caja.mozos.estado', $m) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="px-3 py-2 rounded-xl text-xs font-extrabold"
                                            style="background: {{ $activo ? 'rgba(239,68,68,.10)' : 'rgba(22,163,74,.10)' }};
                                                   color: {{ $activo ? 'rgb(239,68,68)' : 'rgb(22,163,74)' }};">
                                        {{ $activo ? 'Inactivar' : 'Activar' }}
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm" style="color: var(--rf-text-light);">
                            No hay mozos para mostrar.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación si viene paginator --}}
        @if(isset($mozos) && method_exists($mozos, 'links'))
            <div class="px-4 py-3 border-t" style="border-color: var(--rf-border);">
                {{ $mozos->appends(['q' => $q])->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal editar nombre --}}
<div id="modalEditNombre"
     class="fixed inset-0 z-50 hidden items-center justify-center"
     aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-close-edit></div>

    <div class="relative w-full max-w-md mx-4 rounded-2xl border bg-white shadow-xl"
         style="border-color: var(--rf-border);">

        <div class="px-5 py-4 border-b flex items-center justify-between"
             style="border-color: var(--rf-border);">
            <div class="font-extrabold" style="color: var(--rf-text);">
                Editar nombre del mozo
            </div>
            <button type="button" class="text-xl leading-none px-2" style="color: var(--rf-text);" data-close-edit>&times;</button>
        </div>

        <form id="formEditNombre" method="POST" action="">
            @csrf
            @method('PATCH')

            <div class="p-5">
                <label class="text-xs font-bold uppercase tracking-wide" style="color: var(--rf-text-light);">
                    Nombre visible
                </label>
                <input id="inputNombre"
                       type="text"
                       name="name"
                       required
                       maxlength="120"
                       class="mt-1 w-full rounded-xl border px-3 py-2 text-sm outline-none"
                       style="border-color: var(--rf-border); color: var(--rf-text); background: var(--rf-white);">
                <p class="text-xs mt-2" style="color: var(--rf-text-light);">
                    Este nombre es el que se verá en comandas, caja y reportes.
                </p>
            </div>

            <div class="px-5 py-4 border-t flex justify-end gap-2"
                 style="border-color: var(--rf-border);">
                <button type="button"
                        class="px-4 py-2 rounded-xl text-sm font-extrabold border hover:bg-gray-50"
                        style="border-color: var(--rf-border); color: var(--rf-text);"
                        data-close-edit>
                    Cancelar
                </button>

                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-extrabold"
                        style="background: var(--rf-orange); color: white;">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('modalEditNombre');
    const form  = document.getElementById('formEditNombre');
    const input = document.getElementById('inputNombre');

    function openModal(userId, userName) {
        form.action = `{{ url('/admin/caja/mozos') }}/${userId}/nombre`;
        input.value = userName || '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        input.focus();
        input.select();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.querySelectorAll('[data-open-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal(btn.dataset.userId, btn.dataset.userName);
        });
    });

    modal.querySelectorAll('[data-close-edit]').forEach(el => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
@endsection