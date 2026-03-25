<!-- resources/views/layouts/navigation.blade.php -->
@php
    $user = auth()->user();
    $isAdmin = $user && ($user->role ?? null) === 'admin';
    $localId = (int)($user->id_local ?? 0);

    $badgeClass = "ml-2 inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full text-[10px] font-extrabold leading-none shadow-sm";
@endphp

<nav
    x-data="rfNavPoll({
        enabled: {{ $isAdmin ? 'true' : 'false' }},
        url: '{{ $isAdmin ? route('admin.nav.poll') : '' }}',
        intervalMs: 5000
    })"
    x-init="init()"
    class="sticky top-0 z-50 backdrop-blur-md bg-white/95 transition-all duration-200"
    style="border-bottom: 2px solid var(--rf-border); box-shadow: 0 4px 20px rgba(0,0,0,0.02);"
>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">

            {{-- IZQUIERDA --}}
            <div class="flex items-center">

                {{-- LOGO --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <img src="{{ asset('images/refoodlogo.png') }}" 
                             class="h-12 sm:h-16 transition-transform duration-300 group-hover:scale-105" 
                             alt="REFOOD">
                        <span class="hidden lg:inline-block text-xs font-medium px-2 py-0.5 rounded-full bg-orange-50 text-orange-600 border border-orange-100">
                            POS
                        </span>
                    </a>
                </div>

                {{-- LINKS DESKTOP (sin cambios) --}}
                <div class="hidden space-x-2 sm:-my-px sm:ms-8 sm:flex items-center">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="px-4 py-2 rounded-xl hover:bg-orange-50 transition-all">
                        <svg class="w-5 h-5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('Inicio') }}
                    </x-nav-link>

                    @if($isAdmin)

                        {{-- CAJA --}}
                        <x-nav-link :href="route('admin.caja.index')" :active="request()->routeIs('admin.caja.*')" class="px-4 py-2 rounded-xl hover:bg-orange-50 transition-all">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Caja') }}
                                <template x-if="counts.caja_pendientes > 0">
                                    <span class="{{ $badgeClass }}"
                                          style="background: var(--rf-primary); color: white;"
                                          x-text="counts.caja_pendientes">
                                    </span>
                                </template>
                            </span>
                        </x-nav-link>

                        {{-- COMANDAS --}}
                        <x-nav-link :href="route('admin.comandas.index')" :active="request()->routeIs('admin.comandas.*')" class="px-4 py-2 rounded-xl hover:bg-orange-50 transition-all">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                {{ __('Comandas') }}
                                <template x-if="counts.comandas_activas > 0">
                                    <span class="{{ $badgeClass }}"
                                          style="background: #111827; color: white;"
                                          x-text="counts.comandas_activas">
                                    </span>
                                </template>
                            </span>
                        </x-nav-link>

                        {{-- MESAS --}}
                        <x-nav-link :href="route('admin.mesas.index', ['id_local' => $localId ?: 1])"
                                    :active="request()->routeIs('admin.mesas.*')" class="px-4 py-2 rounded-xl hover:bg-orange-50 transition-all">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ __('Mesas') }}
                                <template x-if="counts.mesas_ocupadas > 0">
                                    <span class="{{ $badgeClass }}"
                                          style="background: #dc2626; color: white;"
                                          x-text="counts.mesas_ocupadas">
                                    </span>
                                </template>
                            </span>
                        </x-nav-link>

                        {{-- CARTA --}}
                        <x-nav-link :href="route('admin.carta.index')" :active="request()->routeIs('admin.carta.*')" class="px-4 py-2 rounded-xl hover:bg-orange-50 transition-all">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                {{ __('Carta') }}
                            </span>
                        </x-nav-link>

                    @endif
                </div>
            </div>

            {{-- DERECHA (sin cambios en desktop) --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-5 py-2.5 border-2 text-sm font-bold rounded-xl transition-all duration-200 hover:shadow-lg hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-200"
                            style="background: var(--rf-primary); color: white; border-color: var(--rf-primary-hover);">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="py-2">
                            <!-- Info del usuario -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Conectado como</p>
                                <p class="text-sm font-bold text-gray-700">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->email }}</p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 hover:bg-red-50 flex items-center gap-2 px-4 py-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('Cerrar sesión') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>

                </x-dropdown>
            </div>

            {{-- MOBILE HEADER: Logo + Info usuario + Botón logout + Menú hamburguesa --}}
            <div class="flex items-center gap-2 sm:hidden">
                
                {{-- Información de sesión compacta --}}
                <div class="flex items-center gap-1.5">
                    <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div class="hidden xs:block">
                        <p class="text-xs font-medium text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] text-gray-500 leading-tight">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                {{-- Botón de cerrar sesión (solo icono rojo) --}}
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" 
                            class="p-1.5 rounded-lg transition-all duration-200 hover:bg-red-50 active:bg-red-100"
                            style="color: #EF4444;"
                            title="Cerrar sesión">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>

                {{-- Botón menú hamburguesa --}}
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-1.5 rounded-xl transition-all duration-200 hover:bg-orange-50 active:bg-orange-100"
                        style="color: var(--rf-primary);">
                    <svg x-show="!open" class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- MOBILE MENU - SOLO OPCIONES DE NAVEGACIÓN (solo si hay opciones) --}}
    @if($isAdmin)
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="sm:hidden border-t shadow-lg rounded-b-2xl" 
         style="border-color: var(--rf-border); background: white;"
         x-cloak>

        <div class="py-2 space-y-0.5 px-3">
            
            {{-- Grid de navegación principal (2 columnas) --}}
            <div class="grid grid-cols-2 gap-1.5 pt-2">
                
                {{-- Caja --}}
                <a href="{{ route('admin.caja.index') }}" 
                   class="flex items-center justify-between gap-1 px-2 py-2.5 rounded-lg transition-all hover:bg-orange-50 {{ request()->routeIs('admin.caja.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('Caja') }}</span>
                    </div>
                    <template x-if="counts.caja_pendientes > 0">
                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-bold bg-orange-500 text-white"
                              x-text="counts.caja_pendientes">
                        </span>
                    </template>
                </a>

                {{-- Comandas --}}
                <a href="{{ route('admin.comandas.index') }}" 
                   class="flex items-center justify-between gap-1 px-2 py-2.5 rounded-lg transition-all hover:bg-orange-50 {{ request()->routeIs('admin.comandas.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('Comandas') }}</span>
                    </div>
                    <template x-if="counts.comandas_activas > 0">
                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-bold bg-gray-800 text-white"
                              x-text="counts.comandas_activas">
                        </span>
                    </template>
                </a>

                {{-- Mesas --}}
                <a href="{{ route('admin.mesas.index', ['id_local' => $localId ?: 1]) }}" 
                   class="flex items-center justify-between gap-1 px-2 py-2.5 rounded-lg transition-all hover:bg-orange-50 {{ request()->routeIs('admin.mesas.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('Mesas') }}</span>
                    </div>
                    <template x-if="counts.mesas_ocupadas > 0">
                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-bold bg-red-500 text-white"
                              x-text="counts.mesas_ocupadas">
                        </span>
                    </template>
                </a>

                {{-- Carta --}}
                <a href="{{ route('admin.carta.index') }}" 
                   class="flex items-center gap-2 px-2 py-2.5 rounded-lg transition-all hover:bg-orange-50 {{ request()->routeIs('admin.carta.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="text-sm font-medium">{{ __('Carta') }}</span>
                </a>

            </div>
        </div>
    </div>
    @endif

</nav>

{{-- ========================= --}}
{{-- NAV POLL SCRIPT (sin cambios) --}}
{{-- ========================= --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rfNavPoll', (cfg) => ({
        enabled: !!cfg.enabled,
        url: cfg.url || '',
        intervalMs: Number(cfg.intervalMs || 5000),

        counts: {
            caja_pendientes: 0,
            comandas_activas: 0,
            mesas_ocupadas: 0,
        },

        timer: null,
        busy: false,

        async init() {
            if (!this.enabled || !this.url) return;

            await this.refresh();

            this.timer = setInterval(() => {
                this.refresh();
            }, this.intervalMs);
        },

        async refresh() {
            if (this.busy) return;
            this.busy = true;

            try {
                const res = await fetch(this.url, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (!res.ok) return;

                const data = await res.json();
                if (!data.ok) return;

                this.counts.caja_pendientes = Number(data.caja_pendientes || 0);
                this.counts.comandas_activas = Number(data.comandas_activas || 0);
                this.counts.mesas_ocupadas = Number(data.mesas_ocupadas || 0);

                // Animación sutil cuando se actualizan
                if (this.counts.caja_pendientes > 0 || this.counts.comandas_activas > 0 || this.counts.mesas_ocupadas > 0) {
                    document.querySelectorAll('[class*="badge"]').forEach(badge => {
                        badge.classList.add('animate-pulse');
                        setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
                    });
                }

            } catch (e) {
                // silencioso
            } finally {
                this.busy = false;
            }
        }
    }));
});
</script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Breakpoint para mostrar nombre en móviles con suficiente espacio */
    @media (min-width: 380px) {
        .xs\:block {
            display: block !important;
        }
    }
    @media (max-width: 379px) {
        .xs\:block {
            display: none !important;
        }
    }
</style>