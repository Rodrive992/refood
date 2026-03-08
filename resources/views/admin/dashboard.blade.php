@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold" style="color: #1E293B;">
                Panel Administrador
            </h1>
            <p style="color: #64748B;" class="mt-1">
                Gestión del local · mesas · comandas · carta · caja
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                style="background: #F1F5F9; color: #334155; border: 1px solid #E2E8F0;">
                <span class="w-1.5 h-1.5 rounded-full" style="background: var(--rf-primary);"></span>
                Admin
            </span>
            <span class="hidden sm:inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                style="background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;">
                <svg class="w-3 h-3" style="color: var(--rf-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Local: {{ auth()->user()->id_local ?? '—' }}
            </span>
        </div>
    </div>

    {{-- Módulos principales --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

        {{-- Caja --}}
        <a href="{{ route('admin.caja.index') }}"
           class="group relative rounded-2xl p-6 transition-all duration-300 overflow-hidden bg-white border hover:shadow-xl"
           style="border-color: #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-30" style="background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 70%);"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform" 
                     style="background: #ECFDF5; border: 1px solid #D1FAE5;">
                    <svg class="w-6 h-6" style="color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1" style="color: #0F172A;">Caja</h2>
                <p class="text-sm mb-4" style="color: #475569;">Gestión de turnos, ingresos, egresos y cobros</p>
                <div class="flex items-center text-sm font-medium" style="color: #059669;">
                    Ver caja
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Mesas --}}
        <a href="{{ route('admin.mesas.index') }}"
           class="group relative rounded-2xl p-6 transition-all duration-300 overflow-hidden bg-white border hover:shadow-xl"
           style="border-color: #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-30" style="background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 70%);"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"
                     style="background: #EFF6FF; border: 1px solid #DBEAFE;">
                    <svg class="w-6 h-6" style="color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1" style="color: #0F172A;">Mesas</h2>
                <p class="text-sm mb-4" style="color: #475569;">Configurar disposición del salón y asignación</p>
                <div class="flex items-center text-sm font-medium" style="color: #2563EB;">
                    Armar mesas
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Comandas --}}
        <a href="{{ route('admin.comandas.index') }}"
           class="group relative rounded-2xl p-6 transition-all duration-300 overflow-hidden bg-white border hover:shadow-xl"
           style="border-color: #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-30" style="background: radial-gradient(circle at top right, rgba(217, 119, 6, 0.08), transparent 70%);"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"
                     style="background: #FFFBEB; border: 1px solid #FEF3C7;">
                    <svg class="w-6 h-6" style="color: #D97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1" style="color: #0F172A;">Comandas</h2>
                <p class="text-sm mb-4" style="color: #475569;">Seguimiento de pedidos e impresión para cocina</p>
                <div class="flex items-center text-sm font-medium" style="color: #D97706;">
                    Ver comandas
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Carta --}}
        <a href="{{ route('admin.carta.index') }}"
           class="group relative rounded-2xl p-6 transition-all duration-300 overflow-hidden bg-white border hover:shadow-xl"
           style="border-color: #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-30" style="background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.08), transparent 70%);"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"
                     style="background: #F5F3FF; border: 1px solid #EDE9FE;">
                    <svg class="w-6 h-6" style="color: #7C3AED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-1" style="color: #0F172A;">Carta</h2>
                <p class="text-sm mb-4" style="color: #475569;">Carga de productos, categorías y precios</p>
                <div class="flex items-center text-sm font-medium" style="color: #7C3AED;">
                    Editar carta
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

    </div>

    {{-- Explicación detallada del flujo de trabajo --}}
    <div class="rounded-2xl overflow-hidden mb-8 bg-white border" 
         style="border-color: #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
        
        <div class="p-6" style="border-bottom: 1px solid #F1F5F9;">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <svg class="w-4 h-4" style="color: #F97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-lg font-extrabold" style="color: #0F172A;">Flujo de trabajo del sistema</h2>
            </div>
        </div>

        <div class="p-6 space-y-6">
            
            {{-- 1. Apertura de turno --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">1</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Apertura de turno</h3>
                    <p class="text-sm mb-2" style="color: #475569;">Desde el módulo de <strong style="color: #059669;">Caja</strong> se debe abrir el turno para comenzar la jornada. Durante el turno se pueden realizar ingresos o retiros de dinero.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" 
                              style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Abrir turno
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ingresar
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                            Retirar
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. Activación de mozos --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">2</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Activación de mozos</h3>
                    <p class="text-sm" style="color: #475569;">Una vez abierto el turno, se debe activar los mozos en el boton mozos del panel de caja, se activan los mozos para que puedan acceder al sistema. Solo con turno abierto y mozos activos pueden comenzar a operar.</p>
                </div>
            </div>

            {{-- 3. Gestión de mesas y comandas --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">3</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Mesas y comandas</h3>
                    <p class="text-sm mb-2" style="color: #475569;">Los mozos eligen una mesa, la ocupan y pueden cargar los items desde la carta. Cada pedido genera una <strong style="color: #D97706;">comanda</strong> que puede imprimirse para enviar a cocina.</p>
                    <div class="flex gap-2">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #D97706;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimir comanda
                        </span>
                    </div>
                </div>
            </div>

            {{-- 4. Solicitud de cuenta --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">4</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Solicitud de cuenta</h3>
                    <p class="text-sm mb-2" style="color: #475569;">Cuando los clientes solicitan la cuenta, el mozo genera el pedido que aparece automáticamente en <strong style="color: #059669;">Caja</strong> para su procesamiento.</p>
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                          style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Preticket
                    </span>
                </div>
            </div>

            {{-- 5. Cobro y cierre --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">5</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Cobro y finalización</h3>
                    <p class="text-sm mb-2" style="color: #475569;">En caja se puede imprimir el preticket para mostrar al cliente y luego realizar el cobro final, incluyendo propinas si corresponde.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Cobrar
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #F0FDF4; border: 1px solid #DCFCE7; color: #059669;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Propina
                        </span>
                    </div>
                </div>
            </div>

            {{-- 6. Cierre de turno e historial --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: #FFF7ED; border: 1px solid #FFEDD5;">
                    <span class="text-sm font-bold" style="color: #F97316;">6</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold mb-1" style="color: #0F172A;">Cierre de turno</h3>
                    <p class="text-sm mb-2" style="color: #475569;">Al finalizar la jornada se debe cerrar el turno. Esto inactiva automáticamente a todos los mozos y cierra las comandas activas. Se pueden imprimir todos los movimientos del día.</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #DC2626;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Cerrar turno
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
                              style="background: #EFF6FF; border: 1px solid #DBEAFE; color: #2563EB;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Historial
                        </span>
                    </div>
                    <p class="text-sm" style="color: #475569;">Todo queda guardado en el <strong style="color: #2563EB;">Historial</strong>, donde se puede reimprimir cualquier ticket o consultar turnos anteriores.</p>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection