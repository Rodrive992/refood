<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h2 class="text-xl font-semibold">Carta · REFOOD</h2>
                <p class="text-sm opacity-80">
                    Gestión de categorías e items (Local #{{ auth()->user()->id_local ?? '—' }})
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="px-4 py-2 rounded-lg text-sm border"
                    style="border-color: var(--rf-border); background: var(--rf-white);"
                >
                    ← Volver al dashboard
                </a>

                <button type="button" data-open-modal="modal-create-categoria"
                    class="px-4 py-2 rounded-lg text-white text-sm"
                    style="background: var(--rf-primary);"
                >
                    + Nueva categoría
                </button>

                <button type="button" data-open-modal="modal-create-item"
                    class="px-4 py-2 rounded-lg text-white text-sm"
                    style="background: var(--rf-secondary);"
                >
                    + Nuevo item
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6">
        {{-- Flash --}}
        @if (session('ok'))
            <div class="mb-4 p-4 rounded-xl border"
                style="border-color: var(--rf-border); background: var(--rf-primary-soft); color: var(--rf-primary);">
                ✅ {{ session('ok') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl border"
                style="border-color: var(--rf-border); background: #fff7ed; color: #9a3412;">
                <p class="font-semibold mb-2">Se encontraron errores:</p>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="rounded-xl shadow-sm border p-4 mb-5"
            style="background: var(--rf-white); border-color: var(--rf-border);">
            <form method="GET" action="{{ route('admin.carta.index') }}" class="flex gap-2 flex-wrap items-center">
                <div class="flex-1 min-w-[240px]">
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                        placeholder="Buscar categoría o item (nombre / descripción)..."
                        class="w-full rounded-lg border px-4 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);">
                </div>

                <button type="submit"
                    class="px-4 py-2 rounded-lg text-white text-sm"
                    style="background: var(--rf-primary);">
                    Buscar
                </button>

                <a href="{{ route('admin.carta.index') }}"
                    class="px-4 py-2 rounded-lg text-sm border"
                    style="border-color: var(--rf-border); background: var(--rf-white);">
                    Limpiar
                </a>

                <span class="text-xs px-3 py-1 rounded-full"
                    style="background: var(--rf-secondary-soft); color: var(--rf-secondary);">
                    Categorías: {{ $categorias->count() }} · Items: {{ $items->flatten(1)->count() }}
                </span>
            </form>
        </div>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            {{-- IZQUIERDA: Categorías + Items sin categoría (más ancho) --}}
            <div class="lg:col-span-9 space-y-5">

                {{-- Categorías --}}
                <div class="rounded-xl shadow-sm border p-4"
                    style="background: var(--rf-white); border-color: var(--rf-border);">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Categorías</h3>
                        <span class="text-xs opacity-75">Orden / estado</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($categorias as $cat)
                            <div class="p-3 rounded-xl border"
                                style="border-color: var(--rf-border); background: var(--rf-white);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs px-2 py-0.5 rounded-full"
                                                style="background: var(--rf-primary-soft); color: var(--rf-primary);">
                                                #{{ $cat->orden }}
                                            </span>

                                            <p class="font-semibold break-words leading-tight">
                                                {{ $cat->nombre }}
                                            </p>
                                        </div>

                                        <div class="mt-1 flex items-center gap-2 text-xs">
                                            @if($cat->activo)
                                                <span class="px-2 py-0.5 rounded-full"
                                                    style="background: #ecfdf5; color: #047857;">
                                                    Activa
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full"
                                                    style="background: #fef2f2; color: #b91c1c;">
                                                    Inactiva
                                                </span>
                                            @endif
                                            <span class="opacity-70">
                                                Items: {{ ($items[$cat->id] ?? collect())->count() }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- Toggle --}}
                                        <form method="POST" action="{{ route('admin.carta.categorias.toggle', $cat) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs border"
                                                style="border-color: var(--rf-border); background: var(--rf-white);"
                                                title="Activar / desactivar">
                                                {{ $cat->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>

                                        {{-- Edit --}}
                                        <button type="button"
                                            data-open-modal="modal-edit-categoria"
                                            data-cat-id="{{ $cat->id }}"
                                            data-cat-nombre="{{ e($cat->nombre) }}"
                                            data-cat-orden="{{ $cat->orden }}"
                                            data-cat-activo="{{ $cat->activo ? 1 : 0 }}"
                                            class="px-3 py-1.5 rounded-lg text-xs text-white"
                                            style="background: var(--rf-primary);">
                                            Editar
                                        </button>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.carta.categorias.destroy', $cat) }}"
                                            onsubmit="return confirm('¿Eliminar categoría {{ addslashes($cat->nombre) }}? Los items quedarán sin categoría (NULL).');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs text-white"
                                                style="background: #ef4444;">
                                                Borrar
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <button type="button"
                                        class="text-xs underline opacity-80 hover:opacity-100"
                                        data-toggle-panel="panel-cat-{{ $cat->id }}">
                                        Ver items
                                    </button>

                                    <span class="text-xs opacity-70">
                                        ID: {{ $cat->id }}
                                    </span>
                                </div>
                            </div>

                            {{-- panel items category (colapsable dentro de categorías) --}}
                            <div id="panel-cat-{{ $cat->id }}" class="hidden rounded-xl border p-3 mb-4 overflow-x-auto"
                                style="border-color: var(--rf-border); background: var(--rf-primary-soft);">

                                <div class="text-xs mb-2 opacity-80">
                                    Items en {{ $cat->nombre }} ({{ ($items[$cat->id] ?? collect())->count() }})
                                </div>

                                @php $list = $items[$cat->id] ?? collect(); @endphp

                                @if($list->isEmpty())
                                    <div class="text-sm">No hay items en esta categoría.</div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($list as $it)
                                            <div class="p-3 rounded-xl border"
                                                style="border-color: var(--rf-border); background: var(--rf-white);">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs px-2 py-0.5 rounded-full"
                                                                style="background: var(--rf-secondary-soft); color: var(--rf-secondary);">
                                                                #{{ $it->orden }}
                                                            </span>
                                                            <p class="font-semibold break-words leading-tight">
                                                                {{ $it->nombre }}
                                                            </p>
                                                            @if(!$it->activo)
                                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                                    style="background: #fef2f2; color: #b91c1c;">
                                                                    Inactivo
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="text-xs opacity-80 mt-1">
                                                            $ {{ number_format($it->precio, 2, ',', '.') }}
                                                            @if(!is_null($it->costo))
                                                                · costo $ {{ number_format($it->costo, 2, ',', '.') }}
                                                            @endif
                                                        </div>

                                                        @if($it->descripcion)
                                                            <p class="text-xs opacity-80 mt-1 break-words">
                                                                {{ $it->descripcion }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="flex items-center gap-2 shrink-0">
                                                        {{-- Toggle --}}
                                                        <form method="POST" action="{{ route('admin.carta.items.toggle', $it) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                class="px-3 py-1.5 rounded-lg text-xs border"
                                                                style="border-color: var(--rf-border); background: var(--rf-white);">
                                                                {{ $it->activo ? 'Desactivar' : 'Activar' }}
                                                            </button>
                                                        </form>

                                                        {{-- Edit --}}
                                                        <button type="button"
                                                            data-open-modal="modal-edit-item"
                                                            data-item-id="{{ $it->id }}"
                                                            data-item-id_categoria="{{ $it->id_categoria }}"
                                                            data-item-nombre="{{ e($it->nombre) }}"
                                                            data-item-descripcion="{{ e($it->descripcion ?? '') }}"
                                                            data-item-precio="{{ $it->precio }}"
                                                            data-item-costo="{{ $it->costo ?? '' }}"
                                                            data-item-orden="{{ $it->orden }}"
                                                            data-item-activo="{{ $it->activo ? 1 : 0 }}"
                                                            class="px-3 py-1.5 rounded-lg text-xs text-white"
                                                            style="background: var(--rf-primary);">
                                                            Editar
                                                        </button>

                                                        {{-- Delete --}}
                                                        <form method="POST" action="{{ route('admin.carta.items.destroy', $it) }}"
                                                            onsubmit="return confirm('¿Eliminar item {{ addslashes($it->nombre) }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="px-3 py-1.5 rounded-lg text-xs text-white"
                                                                style="background: #ef4444;">
                                                                Borrar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm opacity-80">
                                No hay categorías todavía. Creá la primera con “Nueva categoría”.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Items sin categoría (queda acá, abajo, y ocupa el ancho “útil”) --}}
                <div class="rounded-xl shadow-sm border p-4"
                    style="background: var(--rf-white); border-color: var(--rf-border);">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Items sin categoría</h3>
                        <span class="text-xs opacity-75">id_categoria = NULL</span>
                    </div>

                    @php $sinCat = $items[null] ?? collect(); @endphp

                    @if($sinCat->isEmpty())
                        <div class="text-sm opacity-80">No hay items sin categoría.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($sinCat as $it)
                                <div class="p-3 rounded-xl border"
                                    style="border-color: var(--rf-border); background: var(--rf-white);">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    style="background: var(--rf-secondary-soft); color: var(--rf-secondary);">
                                                    #{{ $it->orden }}
                                                </span>
                                                <p class="font-semibold break-words leading-tight">{{ $it->nombre }}</p>
                                                @if(!$it->activo)
                                                    <span class="text-xs px-2 py-0.5 rounded-full"
                                                        style="background: #fef2f2; color: #b91c1c;">
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="text-xs opacity-80 mt-1">
                                                $ {{ number_format($it->precio, 2, ',', '.') }}
                                                @if(!is_null($it->costo))
                                                    · costo $ {{ number_format($it->costo, 2, ',', '.') }}
                                                @endif
                                            </div>

                                            @if($it->descripcion)
                                                <p class="text-xs opacity-80 mt-1 break-words">{{ $it->descripcion }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <form method="POST" action="{{ route('admin.carta.items.toggle', $it) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-xs border"
                                                    style="border-color: var(--rf-border); background: var(--rf-white);">
                                                    {{ $it->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            <button type="button"
                                                data-open-modal="modal-edit-item"
                                                data-item-id="{{ $it->id }}"
                                                data-item-id_categoria=""
                                                data-item-nombre="{{ e($it->nombre) }}"
                                                data-item-descripcion="{{ e($it->descripcion ?? '') }}"
                                                data-item-precio="{{ $it->precio }}"
                                                data-item-costo="{{ $it->costo ?? '' }}"
                                                data-item-orden="{{ $it->orden }}"
                                                data-item-activo="{{ $it->activo ? 1 : 0 }}"
                                                class="px-3 py-1.5 rounded-lg text-xs text-white"
                                                style="background: var(--rf-primary);">
                                                Editar
                                            </button>

                                            <form method="POST" action="{{ route('admin.carta.items.destroy', $it) }}"
                                                onsubmit="return confirm('¿Eliminar item {{ addslashes($it->nombre) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-xs text-white"
                                                    style="background: #ef4444;">
                                                    Borrar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- DERECHA: Ayuda mínima (más chica y sin “Próximamente”) --}}
            <div class="lg:col-span-3 space-y-5">
                <div class="rounded-xl border p-3"
                    style="border-color: var(--rf-border); background: var(--rf-secondary-soft);">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-sm font-semibold" style="color: var(--rf-secondary);">
                            Cómo usar
                        </h4>
                        <span class="text-[11px] opacity-75">
                            Tip: <b>Orden</b>
                        </span>
                    </div>

                    <ul class="mt-2 text-xs opacity-85 space-y-1 list-disc pl-4">
                        <li>Creá categorías e items con los botones de arriba.</li>
                        <li>En cada categoría, tocá <b>“Ver items”</b> para desplegar.</li>
                        <li>Los que no tengan categoría aparecen en <b>“Items sin categoría”</b>.</li>
                    </ul>

                    <p class="mt-2 text-[11px] opacity-70 leading-snug">
                        <b>Orden</b> controla el orden de impresión/visualización.
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- ===========================
        MODALES (rf-hidden)
    =========================== --}}

    {{-- MODAL: CREAR CATEGORÍA --}}
    <div id="modal-create-categoria" class="rf-modal rf-hidden">
        <div class="rf-modal-backdrop" data-close-modal></div>
        <div class="rf-modal-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Nueva categoría</h3>
                <button type="button" class="rf-icon-btn" data-close-modal>✕</button>
            </div>

            <form method="POST" action="{{ route('admin.carta.categorias.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="text-sm font-medium">Nombre</label>
                    <input name="nombre" required maxlength="120"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        placeholder="Ej: Pizzas, Bebidas...">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Orden</label>
                        <input name="orden" type="number" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);"
                            placeholder="0">
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input id="cat_create_activo" name="activo" type="checkbox" value="1" checked
                            class="rounded border" style="border-color: var(--rf-border);">
                        <label for="cat_create_activo" class="text-sm">Activa</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        data-close-modal>
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm"
                        style="background: var(--rf-primary);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDITAR CATEGORÍA --}}
    <div id="modal-edit-categoria" class="rf-modal rf-hidden">
        <div class="rf-modal-backdrop" data-close-modal></div>
        <div class="rf-modal-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Editar categoría</h3>
                <button type="button" class="rf-icon-btn" data-close-modal>✕</button>
            </div>

            <form id="form-edit-categoria" method="POST" action="#" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-medium">Nombre</label>
                    <input id="cat_edit_nombre" name="nombre" required maxlength="120"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Orden</label>
                        <input id="cat_edit_orden" name="orden" type="number" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input id="cat_edit_activo" name="activo" type="checkbox" value="1"
                            class="rounded border" style="border-color: var(--rf-border);">
                        <label for="cat_edit_activo" class="text-sm">Activa</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        data-close-modal>
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm"
                        style="background: var(--rf-primary);">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: CREAR ITEM --}}
    <div id="modal-create-item" class="rf-modal rf-hidden">
        <div class="rf-modal-backdrop" data-close-modal></div>
        <div class="rf-modal-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Nuevo item</h3>
                <button type="button" class="rf-icon-btn" data-close-modal>✕</button>
            </div>

            <form method="POST" action="{{ route('admin.carta.items.store') }}" class="space-y-3">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Categoría</label>
                        <select name="id_categoria"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                            <option value="">— Sin categoría —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input id="item_create_activo" name="activo" type="checkbox" value="1" checked
                            class="rounded border" style="border-color: var(--rf-border);">
                        <label for="item_create_activo" class="text-sm">Activo</label>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Nombre</label>
                    <input name="nombre" required maxlength="180"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        placeholder="Ej: Pizza Muzza">
                </div>

                <div>
                    <label class="text-sm font-medium">Descripción (opcional)</label>
                    <textarea name="descripcion" rows="3"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        placeholder="Ej: Muzarella, tomate, aceitunas..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-sm font-medium">Precio</label>
                        <input name="precio" required type="number" step="0.01" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Costo (opcional)</label>
                        <input name="costo" type="number" step="0.01" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Orden</label>
                        <input name="orden" type="number" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);"
                            placeholder="0">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        data-close-modal>
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm"
                        style="background: var(--rf-secondary);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDITAR ITEM --}}
    <div id="modal-edit-item" class="rf-modal rf-hidden">
        <div class="rf-modal-backdrop" data-close-modal></div>
        <div class="rf-modal-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Editar item</h3>
                <button type="button" class="rf-icon-btn" data-close-modal>✕</button>
            </div>

            <form id="form-edit-item" method="POST" action="#" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Categoría</label>
                        <select id="item_edit_id_categoria" name="id_categoria"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                            <option value="">— Sin categoría —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input id="item_edit_activo" name="activo" type="checkbox" value="1"
                            class="rounded border" style="border-color: var(--rf-border);">
                        <label for="item_edit_activo" class="text-sm">Activo</label>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Nombre</label>
                    <input id="item_edit_nombre" name="nombre" required maxlength="180"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);">
                </div>

                <div>
                    <label class="text-sm font-medium">Descripción (opcional)</label>
                    <textarea id="item_edit_descripcion" name="descripcion" rows="3"
                        class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--rf-border); background: var(--rf-white);"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-sm font-medium">Precio</label>
                        <input id="item_edit_precio" name="precio" required type="number" step="0.01" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Costo (opcional)</label>
                        <input id="item_edit_costo" name="costo" type="number" step="0.01" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Orden</label>
                        <input id="item_edit_orden" name="orden" type="number" min="0"
                            class="w-full mt-1 rounded-lg border px-3 py-2 text-sm"
                            style="border-color: var(--rf-border); background: var(--rf-white);">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color: var(--rf-border); background: var(--rf-white);"
                        data-close-modal>
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm"
                        style="background: var(--rf-primary);">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===========================
        Estilos + JS (rf-hidden)
    =========================== --}}
    <style>
        .rf-hidden { display: none !important; }

        .rf-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            place-items: center;
            padding: 1rem;
        }
        .rf-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.45); }
        .rf-modal-card {
            position: relative;
            width: 100%;
            max-width: 760px;
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--rf-border);
            background: var(--rf-white);
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .rf-icon-btn{
            width: 36px; height: 36px;
            border-radius: 10px;
            border: 1px solid var(--rf-border);
            background: var(--rf-white);
        }
    </style>

    <script>
        (function () {
            function openModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('rf-hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(modalEl) {
                if (!modalEl) return;
                modalEl.classList.add('rf-hidden');
                document.body.style.overflow = '';
            }

            // Forzar: al cargar, cerramos todos los modales
            document.querySelectorAll('.rf-modal').forEach(m => m.classList.add('rf-hidden'));

            // Open modal
            document.querySelectorAll('[data-open-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modalId = btn.getAttribute('data-open-modal');
                    if (!modalId) return;

                    // Edit categoria
                    if (modalId === 'modal-edit-categoria') {
                        const id = btn.getAttribute('data-cat-id');
                        const nombre = btn.getAttribute('data-cat-nombre') || '';
                        const orden = btn.getAttribute('data-cat-orden') || '0';
                        const activo = btn.getAttribute('data-cat-activo') === '1';

                        const form = document.getElementById('form-edit-categoria');
                        if (form && id) form.action = `{{ url('/admin/carta/categorias') }}/${id}`;

                        const inputNombre = document.getElementById('cat_edit_nombre');
                        const inputOrden = document.getElementById('cat_edit_orden');
                        const inputActivo = document.getElementById('cat_edit_activo');

                        if (inputNombre) inputNombre.value = nombre;
                        if (inputOrden) inputOrden.value = orden;
                        if (inputActivo) inputActivo.checked = !!activo;
                    }

                    // Edit item
                    if (modalId === 'modal-edit-item') {
                        const id = btn.getAttribute('data-item-id');
                        const idCat = btn.getAttribute('data-item-id_categoria') || '';
                        const nombre = btn.getAttribute('data-item-nombre') || '';
                        const descripcion = btn.getAttribute('data-item-descripcion') || '';
                        const precio = btn.getAttribute('data-item-precio') || '0';
                        const costo = btn.getAttribute('data-item-costo') || '';
                        const orden = btn.getAttribute('data-item-orden') || '0';
                        const activo = btn.getAttribute('data-item-activo') === '1';

                        const form = document.getElementById('form-edit-item');
                        if (form && id) form.action = `{{ url('/admin/carta/items') }}/${id}`;

                        const selCat = document.getElementById('item_edit_id_categoria');
                        const inNombre = document.getElementById('item_edit_nombre');
                        const inDesc = document.getElementById('item_edit_descripcion');
                        const inPrecio = document.getElementById('item_edit_precio');
                        const inCosto = document.getElementById('item_edit_costo');
                        const inOrden = document.getElementById('item_edit_orden');
                        const inActivo = document.getElementById('item_edit_activo');

                        if (selCat) selCat.value = idCat;
                        if (inNombre) inNombre.value = nombre;
                        if (inDesc) inDesc.value = descripcion;
                        if (inPrecio) inPrecio.value = precio;
                        if (inCosto) inCosto.value = costo;
                        if (inOrden) inOrden.value = orden;
                        if (inActivo) inActivo.checked = !!activo;
                    }

                    openModal(modalId);
                });
            });

            // Close modal
            document.querySelectorAll('[data-close-modal]').forEach(el => {
                el.addEventListener('click', () => {
                    const modal = el.closest('.rf-modal');
                    closeModal(modal);
                });
            });

            // ESC closes
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                document.querySelectorAll('.rf-modal').forEach(modal => closeModal(modal));
            });

            // Toggle panels
            document.querySelectorAll('[data-toggle-panel]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-toggle-panel');
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.classList.toggle('hidden');
                });
            });
        })();
    </script>
</x-app-layout>
