<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'REFOOD') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- REFOOD Palette -->
    <style>
        :root {
            /* Naranja */
            --rf-primary: #F97316;
            --rf-primary-hover: #EA580C;
            --rf-primary-soft: #FFEDD5;

            /* Verde */
            --rf-secondary: #16A34A;
            --rf-secondary-hover: #15803D;
            --rf-secondary-soft: #DCFCE7;

            /* Neutros */
            --rf-bg: #F9FAFB;
            --rf-white: #FFFFFF;
            --rf-text: #374151;
            --rf-border: #E5E7EB;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" style="background-color: var(--rf-bg); color: var(--rf-text);">
    <div class="min-h-screen">

        @include('layouts.navigation')

        @php
            $headerSection = trim($__env->yieldContent('header'));
            $hasHeaderSection = $headerSection !== '';

            // ✅ NUEVO: ancho configurable por vista
            // default: max-w-7xl (como venías usando)
            $pageWidth = trim($__env->yieldContent('page_width')) ?: 'max-w-7xl';
        @endphp

        @if(isset($header) || $hasHeaderSection)
            <header style="background: var(--rf-white); border-bottom: 1px solid var(--rf-border);">
                <div class="{{ $pageWidth }} mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @isset($header)
                        {{ $header }}
                    @endisset

                    @if($hasHeaderSection)
                        @yield('header')
                    @endif
                </div>
            </header>
        @endif

        <main class="py-8">
            <div class="{{ $pageWidth }} mx-auto px-4 md:px-6">
                @isset($slot)
                    {{ $slot }}
                @endisset

                @yield('content')
            </div>
        </main>

    </div>
</body>
</html>
