<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'REFOOD')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

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

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
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

        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .animate-slide-in-right { animation: slideInRight 0.3s ease-out; }
        .animate-slide-in-left { animation: slideInLeft 0.3s ease-out; }
        .animate-fade-out { animation: fadeOut 0.3s ease-out; }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .animate-scale-in { animation: scaleIn 0.2s ease-out; }

        /* ====================
           UTILIDADES GLOBALES
        ==================== */
        .rf-transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .rf-hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .rf-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        .rf-hover-scale {
            transition: transform 0.2s ease;
        }

        .rf-hover-scale:hover {
            transform: scale(1.02);
        }

        .rf-gradient-primary {
            background: linear-gradient(135deg, var(--rf-primary), var(--rf-primary-hover));
        }

        .rf-gradient-secondary {
            background: linear-gradient(135deg, var(--rf-secondary), var(--rf-secondary-hover));
        }

        .rf-gradient-success {
            background: linear-gradient(135deg, var(--rf-success), #059669);
        }

        .rf-gradient-warning {
            background: linear-gradient(135deg, var(--rf-warning), #D97706);
        }

        .rf-gradient-error {
            background: linear-gradient(135deg, var(--rf-error), #DC2626);
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

        /* Cards y contenedores */
        .rf-card {
            background: var(--rf-white);
            border-radius: 1rem;
            border: 1px solid var(--rf-border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .rf-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        .rf-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--rf-border);
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.03), transparent);
            font-weight: 600;
            border-radius: 1rem 1rem 0 0;
        }

        .rf-card-body {
            padding: 1.5rem;
        }

        /* Badges mejorados */
        .rf-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            background: var(--rf-primary-soft);
            color: var(--rf-primary-hover);
        }

        .rf-badge-success {
            background: var(--rf-secondary-soft);
            color: var(--rf-secondary-hover);
        }

        .rf-badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .rf-badge-error {
            background: #FEE2E2;
            color: #B91C1C;
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

        /* Estilos para inputs y selects */
        .rf-input {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid var(--rf-border);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            outline: none;
        }

        .rf-input:focus {
            border-color: var(--rf-primary);
            box-shadow: 0 0 0 3px var(--rf-primary-soft);
        }

        .rf-input.error {
            border-color: var(--rf-error);
        }

        .rf-input.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* Botones mejorados */
        .rf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            outline: none;
        }

        .rf-btn-primary {
            background: var(--rf-primary);
            color: white;
        }

        .rf-btn-primary:hover {
            background: var(--rf-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.3);
        }

        .rf-btn-secondary {
            background: var(--rf-secondary);
            color: white;
        }

        .rf-btn-secondary:hover {
            background: var(--rf-secondary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.3);
        }

        .rf-btn-outline {
            background: transparent;
            border: 1px solid var(--rf-border);
            color: var(--rf-text);
        }

        .rf-btn-outline:hover {
            background: var(--rf-bg);
            border-color: var(--rf-primary);
            color: var(--rf-primary);
        }

        /* Mejoras para tablas */
        .rf-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .rf-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--rf-text-light);
            background: var(--rf-bg);
            border-bottom: 1px solid var(--rf-border);
        }

        .rf-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--rf-border-light);
        }

        .rf-table tbody tr:hover {
            background: var(--rf-bg);
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

            .rf-mobile-sticky-bottom {
                position: sticky;
                bottom: 0;
                background: var(--rf-white);
                border-top: 1px solid var(--rf-border);
                padding: 1rem;
                z-index: 30;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased h-full">
    <div class="min-h-screen flex flex-col bg-gradient-to-br from-gray-50 to-white">
        @include('layouts.navigation')

        @php
            $headerSection = trim($__env->yieldContent('header'));
            $hasHeaderSection = $headerSection !== '';
            $pageWidth = trim($__env->yieldContent('page_width')) ?: 'max-w-7xl';
        @endphp

        @if(isset($header) || $hasHeaderSection)
            <header class="sticky top-0 z-40 backdrop-blur-sm bg-white/90" style="border-bottom: 1px solid var(--rf-border);">
                <div class="{{ $pageWidth }} mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            @isset($header)
                                {{ $header }}
                            @endisset

                            @if($hasHeaderSection)
                                @yield('header')
                            @endif
                        </div>

                        @hasSection('header-actions')
                            <div class="ml-4">
                                @yield('header-actions')
                            </div>
                        @endif
                    </div>
                </div>
            </header>
        @endif

        <main class="flex-1 relative">
            <!-- Decoración de fondo sutil -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-orange-100/20 to-transparent rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-green-100/20 to-transparent rounded-full blur-3xl"></div>
            </div>

            <!-- Contenido principal -->
            <div class="relative">
                @isset($slot)
                    {{ $slot }}
                @endisset

                @yield('content')
            </div>
        </main>

        {{-- Toast container mejorado --}}
        <div id="toastContainer" class="fixed bottom-6 right-6 z-50 space-y-3"></div>
    </div>

    {{-- MODAL GLOBAL ÚNICO: Solicitar Cuenta --}}
    <div id="cuentaModal"
         class="rf-modal-backdrop fixed inset-0 z-50 hidden items-end md:items-center justify-center p-4 transition-all duration-300"
         style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-scale-in border"
             style="border-color: var(--rf-border); transform-origin: center;">

            <div class="px-6 py-5 border-b flex items-center justify-between"
                 style="border-color: var(--rf-border); background: linear-gradient(90deg, rgba(249, 115, 22, 0.05), rgba(255,255,255,1));">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wider" style="color: var(--rf-primary);">Solicitar cuenta</div>
                    <div class="text-xl font-extrabold" style="color: var(--rf-text);">
                        <span id="cuentaMesa" class="text-2xl">—</span> <span class="text-gray-300 mx-2">·</span> Comanda <span class="text-orange-500">#<span id="cuentaComanda">—</span></span>
                    </div>
                </div>

                <button type="button"
                        data-action="close-modal"
                        data-modal="cuentaModal"
                        class="h-10 w-10 rounded-xl border flex items-center justify-center rf-hover-lift hover:bg-gray-50 transition-all"
                        style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="cuentaForm" method="POST" action="" class="p-6">
                @csrf

                <div class="mb-6 p-6 rounded-xl border-2"
                     style="border-color: var(--rf-primary-soft); background: linear-gradient(135deg, var(--rf-bg), white);">
                    <div class="text-center">
                        <div class="text-xs uppercase tracking-wider mb-1" style="color: var(--rf-text-light);">Total estimado</div>
                        <div class="text-4xl font-black" style="color: var(--rf-primary);">
                            $ <span id="cuentaSubtotal">0</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold mb-2" style="color: var(--rf-text);">
                        Nota para caja <span class="font-normal text-gray-400">(opcional)</span>
                    </label>
                    <textarea name="nota" rows="3" maxlength="255"
                              class="w-full px-4 py-3 rounded-xl border focus:outline-none focus:ring-2 focus:ring-orange-200 resize-none transition-all"
                              style="border-color: var(--rf-border); background: white;"
                              placeholder="Ej: pagan separados / transferencia / separar bebidas"></textarea>
                    <div class="text-xs mt-2 flex items-center gap-1" style="color: var(--rf-text-light);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aclaraciones de pago o indicaciones especiales
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            data-action="close-modal"
                            data-modal="cuentaModal"
                            class="flex-1 px-4 py-3 font-extrabold border rounded-xl hover:bg-gray-50 transition-all"
                            style="border-color: var(--rf-border); background: var(--rf-white); color: var(--rf-text);">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="flex-1 px-4 py-3 text-white font-extrabold rounded-xl rf-hover-lift flex items-center justify-center gap-2"
                            style="background: var(--rf-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
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