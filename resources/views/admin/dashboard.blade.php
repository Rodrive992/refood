@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">
                Panel Administrador
            </h1>
            <p class="text-slate-600 mt-1">
                Gestión del local · mesas · comandas · carta · caja
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                style="background: var(--rf-primary-soft); color: var(--rf-primary);">
                ● Admin
            </span>
            <span class="hidden sm:inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                style="background: var(--rf-secondary-soft); color: var(--rf-secondary);">
                Local: {{ auth()->user()->id_local ?? '—' }}
            </span>
        </div>
    </div>

    {{-- Accesos rápidos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Caja (NUEVO) --}}
        <a href="{{ route('admin.caja.index') }}"
           class="group rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">
                        Caja
                    </h2>
                    <p class="text-sm text-slate-700 mt-1">
                        Cuentas solicitadas · imprimir · cobrar
                    </p>
                </div>
                <div class="text-3xl">💳</div>
            </div>
            <div class="mt-4 text-sm font-extrabold text-emerald-700">
                Ir a caja →
            </div>

            {{-- Tip visual --}}
            <div class="mt-3 text-xs text-emerald-800/80">
                Ver por mesa las comandas en <strong>cerrando</strong> (cuenta pedida)
            </div>
        </a>

        {{-- Mesas --}}
        <a href="{{ route('admin.mesas.index') }}"
           class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">
                        Mesas
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Estado del salón
                    </p>
                </div>
                <div class="text-3xl">🍽️</div>
            </div>
            <div class="mt-4 text-sm font-semibold text-emerald-700">
                Ver mesas →
            </div>
        </a>

        {{-- Comandas --}}
        <a href="{{ route('admin.comandas.index') }}"
           class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">
                        Comandas
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Activas y cierres
                    </p>
                </div>
                <div class="text-3xl">🧾</div>
            </div>
            <div class="mt-4 text-sm font-semibold text-emerald-700">
                Ver comandas →
            </div>
        </a>

        {{-- Carta --}}
        <a href="{{ route('admin.carta.index') }}"
           class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">
                        Carta
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">
                        Categorías e ítems
                    </p>
                </div>
                <div class="text-3xl">📋</div>
            </div>
            <div class="mt-4 text-sm font-semibold text-emerald-700">
                Editar carta →
            </div>
        </a>

    </div>

    {{-- Ayuda rápida --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5">
        <h3 class="text-lg font-extrabold text-slate-900 mb-2">
            Flujo recomendado
        </h3>

        <ol class="list-decimal list-inside text-slate-700 space-y-1">
            <li>Configurá las <strong>mesas</strong> del salón</li>
            <li>Cargá y ordená la <strong>carta</strong></li>
            <li>El mozo trabaja la <strong>comanda</strong> por mesa</li>
            <li>Cuando tocan <strong>Solicitar cuenta</strong>, aparece en <strong>Caja</strong></li>
            <li><strong>Caja</strong> imprime la cuenta y realiza el <strong>cobro</strong> (venta + ticket)</li>
        </ol>
    </div>

</div>
@endsection
