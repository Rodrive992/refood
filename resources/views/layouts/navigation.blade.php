@php
    $user = auth()->user();
    $isAdmin = $user && ($user->role ?? null) === 'admin';
    $localId = (int)($user->id_local ?? 0);

    $badgeClass = "ml-2 inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 rounded-full text-[11px] font-extrabold leading-none";
@endphp

<nav
    x-data="rfNavPoll({
        enabled: {{ $isAdmin ? 'true' : 'false' }},
        url: '{{ $isAdmin ? route('admin.nav.poll') : '' }}',
        intervalMs: 5000
    })"
    x-init="init()"
    style="background: var(--rf-white); border-bottom: 1px solid var(--rf-border);"
>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- IZQUIERDA --}}
            <div class="flex items-center">

                {{-- LOGO --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/refoodlogo.png') }}" class="h-20" alt="REFOOD">
                    </a>
                </div>

                {{-- LINKS DESKTOP --}}
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Inicio') }}
                    </x-nav-link>

                    @if($isAdmin)

                        {{-- CAJA --}}
                        <x-nav-link :href="route('admin.caja.index')" :active="request()->routeIs('admin.caja.*')">
                            <span class="inline-flex items-center">
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
                        <x-nav-link :href="route('admin.comandas.index')" :active="request()->routeIs('admin.comandas.*')">
                            <span class="inline-flex items-center">
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
                                    :active="request()->routeIs('admin.mesas.*')">
                            <span class="inline-flex items-center">
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
                        <x-nav-link :href="route('admin.carta.index')" :active="request()->routeIs('admin.carta.*')">
                            {{ __('Carta') }}
                        </x-nav-link>

                    @endif
                </div>
            </div>

            {{-- DERECHA --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl transition duration-150"
                            style="background: var(--rf-primary); color: white;">
                            <div class="font-semibold">{{ Auth::user()->name }}</div>
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
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-red-600 hover:bg-red-50">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>

                </x-dropdown>
            </div>

            {{-- MOBILE BUTTON --}}
            <div class="flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-3 rounded-xl transition duration-150"
                        style="color: var(--rf-primary);">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-show="open" class="sm:hidden border-t" style="border-color: var(--rf-border);">

        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>

            @if($isAdmin)

                <x-responsive-nav-link :href="route('admin.caja.index')">
                    <span class="inline-flex items-center">
                        {{ __('Caja') }}
                        <template x-if="counts.caja_pendientes > 0">
                            <span class="{{ $badgeClass }}"
                                  style="background: var(--rf-primary); color: white;"
                                  x-text="counts.caja_pendientes">
                            </span>
                        </template>
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.comandas.index')">
                    <span class="inline-flex items-center">
                        {{ __('Comandas') }}
                        <template x-if="counts.comandas_activas > 0">
                            <span class="{{ $badgeClass }}"
                                  style="background: #111827; color: white;"
                                  x-text="counts.comandas_activas">
                            </span>
                        </template>
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.mesas.index', ['id_local' => $localId ?: 1])">
                    <span class="inline-flex items-center">
                        {{ __('Mesas') }}
                        <template x-if="counts.mesas_ocupadas > 0">
                            <span class="{{ $badgeClass }}"
                                  style="background: #dc2626; color: white;"
                                  x-text="counts.mesas_ocupadas">
                            </span>
                        </template>
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.carta.index')">
                    {{ __('Carta') }}
                </x-responsive-nav-link>

            @endif

        </div>
    </div>

</nav>

{{-- ========================= --}}
{{-- NAV POLL SCRIPT (UNA VEZ) --}}
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

            } catch (e) {
                // silencioso
            } finally {
                this.busy = false;
            }
        }
    }));
});
</script>