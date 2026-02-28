@extends('layouts.app')

@section('content')
@php
    $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
    $mozoNombre = $comanda->mozo->name ?? ('Mozo #' . ($comanda->id_mozo ?? '-'));

    // fecha/hora
    $openedAt = $comanda->opened_at ? \Carbon\Carbon::parse($comanda->opened_at)->format('d/m/Y H:i') : '—';

    // items
    $items = $comanda->items ?? collect();
@endphp

<style>
    /* “Ticket” */
    .ticket-wrap{
        max-width: 420px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
    }
    .ticket-head{
        padding: 16px 18px;
        border-bottom: 1px dashed #e5e7eb;
    }
    .ticket-body{
        padding: 14px 18px;
    }
    .mono{
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
    }
    @media print{
        header, nav, footer, .no-print { display:none !important; }
        body { background:#fff !important; }
        .ticket-wrap { border: none !important; border-radius: 0 !important; }
        .ticket-head { border-bottom: 1px dashed #000 !important; }
    }
</style>

<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="no-print flex items-center justify-between mb-4">
        <a href="{{ route('admin.comandas.index') }}"
           class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700">
            ← Volver
        </a>

        <div class="flex gap-2">
            <button onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold">
                🖨️ Imprimir
            </button>
            <button onclick="window.close()"
                    class="px-4 py-2 rounded-xl bg-slate-900 hover:opacity-90 text-white font-extrabold">
                Cerrar
            </button>
        </div>
    </div>

    <div class="ticket-wrap">
        <div class="ticket-head">
            <div class="text-xs text-slate-500">REFOOD · Ticket cocina</div>
            <div class="text-2xl font-extrabold text-slate-900 mono">COMANDA #{{ $comanda->id }}</div>

            <div class="mt-2 text-sm text-slate-700">
                <div><b>Mesa:</b> {{ $mesaNombre }}</div>
                <div><b>Mozo:</b> {{ $mozoNombre }}</div>
                <div><b>Apertura:</b> {{ $openedAt }}</div>
            </div>

            @if(!empty($comanda->observacion))
                <div class="mt-3 text-sm">
                    <div class="text-xs font-bold text-slate-500">OBSERVACIÓN</div>
                    <div class="mono text-slate-900">“{{ $comanda->observacion }}”</div>
                </div>
            @endif
        </div>

        <div class="ticket-body">
            <div class="text-xs font-bold text-slate-500 mb-2">ITEMS</div>

            @if($items->count())
                <div class="space-y-3">
                    @foreach($items as $it)
                        @php
                            $cant = rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.');
                        @endphp

                        <div class="border-b border-slate-100 pb-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="mono font-extrabold text-slate-900">
                                        {{ $cant }}x {{ $it->nombre_snapshot }}
                                    </div>

                                    @if(!empty($it->nota))
                                        <div class="mono text-sm text-slate-700 mt-1">
                                            Nota: “{{ $it->nota }}”
                                        </div>
                                    @endif

                                    @if(!empty($it->estado))
                                        <div class="text-xs text-slate-500 mt-1">
                                            Estado: <b>{{ $it->estado }}</b>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-slate-600">No hay items.</div>
            @endif

            <div class="mt-4 pt-2 border-t border-slate-200">
                <div class="text-xs text-slate-500">
                    Impreso: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // auto print (suave): da 400ms para que cargue estilos
    window.addEventListener('load', function(){
        setTimeout(() => {
            try { window.print(); } catch(e) {}
        }, 400);
    });
</script>
@endsection