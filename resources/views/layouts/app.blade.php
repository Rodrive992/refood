<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'REFOOD') }} - POS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- REFOOD Palette & Base Styles -->
    <style>
        :root {
            /* Naranja */
            --rf-primary: #F97316;
            --rf-primary-hover: #EA580C;
            --rf-primary-soft: #FFEDD5;
            --rf-primary-light: #FDBA74;

            /* Verde */
            --rf-secondary: #16A34A;
            --rf-secondary-hover: #15803D;
            --rf-secondary-soft: #DCFCE7;
            --rf-secondary-light: #86EFAC;

            /* Neutros */
            --rf-bg: #F9FAFB;
            --rf-white: #FFFFFF;
            --rf-text: #374151;
            --rf-text-light: #6B7280;
            --rf-border: #E5E7EB;
            --rf-border-light: #F3F4F6;

            /* Estados */
            --rf-success: #10B981;
            --rf-warning: #F59E0B;
            --rf-error: #EF4444;
            --rf-info: #3B82F6;
        }

        /* ====================
           ANIMACIONES GLOBALES
        ==================== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .animate-slide-in-right { animation: slideInRight 0.3s ease-out; }
        .animate-fade-out { animation: fadeOut 0.3s ease-out; }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

        /* ====================
           UTILIDADES GLOBALES
        ==================== */
        .rf-transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .rf-hover-lift {
            transition: transform 0.2s ease;
        }

        .rf-hover-lift:hover {
            transform: translateY(-2px);
        }

        .rf-gradient-primary {
            background: linear-gradient(135deg, var(--rf-primary), var(--rf-primary-hover));
        }

        .rf-gradient-secondary {
            background: linear-gradient(135deg, var(--rf-secondary), var(--rf-secondary-hover));
        }

        /* Scroll personalizado */
        .rf-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .rf-scrollbar::-webkit-scrollbar-track {
            background: var(--rf-border-light);
            border-radius: 4px;
        }

        .rf-scrollbar::-webkit-scrollbar-thumb {
            background: var(--rf-primary-light);
            border-radius: 4px;
        }

        .rf-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--rf-primary);
        }

        /* Mejoras de selección */
        ::selection {
            background-color: var(--rf-primary-soft);
            color: var(--rf-primary-hover);
        }

        /* Reset básico para el body */
        body {
            background-color: var(--rf-bg);
            color: var(--rf-text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ====================
           ESTILOS ESPECÍFICOS PARA DASHBOARD MOZO
        ==================== */
        @media (max-width: 768px) {
            .rf-mobile-tab-active {
                border-bottom-color: var(--rf-primary) !important;
                color: var(--rf-primary) !important;
                font-weight: 600;
            }

            .rf-mobile-tab-inactive {
                border-bottom-color: transparent !important;
                color: var(--rf-text-light) !important;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased h-full">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        @php
            $headerSection = trim($__env->yieldContent('header'));
            $hasHeaderSection = $headerSection !== '';
            $pageWidth = trim($__env->yieldContent('page_width')) ?: 'max-w-7xl';
        @endphp

        @if(isset($header) || $hasHeaderSection)
            <header class="sticky top-0 z-40 shadow-sm" style="background: var(--rf-white); border-bottom: 1px solid var(--rf-border);">
                <div class="{{ $pageWidth }} mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    @isset($header)
                        {{ $header }}
                    @endisset

                    @if($hasHeaderSection)
                        @yield('header')
                    @endif
                </div>
            </header>
        @endif

        <main class="flex-1">
            @isset($slot)
                {{ $slot }}
            @endisset

            @yield('content')
        </main>

        {{-- Toast container --}}
        <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    </div>

    {{-- MODAL GLOBAL ÚNICO: Solicitar Cuenta --}}
    <div id="cuentaModal"
         class="rf-modal-backdrop fixed inset-0 z-50 hidden items-end md:items-center justify-center p-4"
         style="background: rgba(0,0,0,0.45);">

        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-fade-in border"
             style="border-color: var(--rf-border);">

            <div class="px-6 py-5 border-b flex items-center justify-between"
                 style="border-color: var(--rf-border); background: linear-gradient(90deg, rgba(34,197,94,.10), rgba(255,255,255,1));">
                <div>
                    <div class="text-xs" style="color: var(--rf-text-light);">Solicitar cuenta</div>
                    <div class="text-xl font-extrabold" style="color: var(--rf-text);">
                        <span id="cuentaMesa">—</span> · Comanda #<span id="cuentaComanda">—</span>
                    </div>
                </div>

                <button type="button"
                        data-action="close-modal"
                        data-modal="cuentaModal"
                        class="h-10 w-10 rounded-2xl border flex items-center justify-center rf-hover-lift"
                        style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                    ✕
                </button>
            </div>

            <form id="cuentaForm" method="POST" action="" class="p-6">
                @csrf

                <div class="mb-6 p-4 rounded-xl border"
                     style="border-color: var(--rf-border); background: var(--rf-bg);">
                    <div class="text-center">
                        <div class="text-xs mb-1" style="color: var(--rf-text-light);">Total estimado</div>
                        <div class="text-3xl font-extrabold" style="color: var(--rf-text);">
                            $ <span id="cuentaSubtotal">0</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold mb-2" style="color: var(--rf-text);">
                        Nota para caja (opcional)
                    </label>
                    <textarea name="nota" rows="3" maxlength="255"
                              class="w-full px-4 py-3 rounded-xl border focus:outline-none resize-none"
                              style="border-color: var(--rf-border); background: white;"
                              placeholder="Ej: pagan separados / transferencia / separar bebidas"></textarea>
                    <div class="text-xs mt-2" style="color: var(--rf-text-light);">
                        Tip: aclaraciones de pago o indicaciones especiales.
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            data-action="close-modal"
                            data-modal="cuentaModal"
                            class="flex-1 px-4 py-3 font-extrabold border rounded-xl"
                            style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="flex-1 px-4 py-3 text-white font-extrabold rounded-xl rf-hover-lift"
                            style="background: var(--rf-primary);">
                        Confirmar solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>

    @stack('modals')

    {{-- SCRIPTS GLOBALES (incluye lógica del modal) --}}
    <script>
        document.addEventListener('click', function (e) {

            // Abrir modal solicitar cuenta
            const openBtn = e.target.closest('[data-action="open-cuenta-modal"]');
            if (openBtn) {
                const modalId = openBtn.dataset.modal || 'cuentaModal';
                const modal = document.getElementById(modalId);
                if (!modal) return;

                const mesaNombre  = openBtn.dataset.mesaNombre || '—';
                const comandaId   = openBtn.dataset.comandaId || '—';
                const subtotalFmt = openBtn.dataset.subtotalFmt || '0';
                const actionUrl   = openBtn.dataset.actionUrl || '';

                const mesaEl = modal.querySelector('#cuentaMesa');
                const comEl  = modal.querySelector('#cuentaComanda');
                const subEl  = modal.querySelector('#cuentaSubtotal');
                const formEl = modal.querySelector('#cuentaForm');

                if (mesaEl) mesaEl.textContent = mesaNombre;
                if (comEl)  comEl.textContent  = comandaId;
                if (subEl)  subEl.textContent  = subtotalFmt;

                if (formEl && actionUrl) {
                    formEl.setAttribute('action', actionUrl);
                }

                // Limpiar nota (opcional, para no arrastrar texto entre comandas)
                const nota = formEl ? formEl.querySelector('[name="nota"]') : null;
                if (nota) nota.value = '';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                return;
            }

            // Cerrar modal (botón)
            const closeBtn = e.target.closest('[data-action="close-modal"]');
            if (closeBtn) {
                const modalId = closeBtn.dataset.modal;
                const modal = document.getElementById(modalId);
                if (!modal) return;

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                return;
            }

            // Cerrar clickeando el backdrop
            const backdrop = e.target.classList && e.target.classList.contains('rf-modal-backdrop') ? e.target : null;
            if (backdrop) {
                backdrop.classList.add('hidden');
                backdrop.classList.remove('flex');
                return;
            }
        });
    </script>

    @stack('scripts')
</body>
</html>