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
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }
        
        .animate-fade-out {
            animation: fadeOut 0.3s ease-out;
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

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

    @stack('modals')
    @stack('scripts')
</body>
</html>