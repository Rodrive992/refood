<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turno Caja #{{ $caja->turno }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 6px;
            background: #fff;
            color: #000;
            font-family: monospace;
            font-size: 11px;
            line-height: 1.25;
        }

        .ticket {
            width: 76mm;
            max-width: 76mm;
            margin: 0 auto;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .small { font-size: 10px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
        }

        .row > span:first-child {
            flex: 1;
            min-width: 0;
        }

        .row > span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .wrap {
            white-space: normal;
            word-break: break-word;
        }

        .section-title {
            font-weight: 700;
            margin: 6px 0 4px;
            text-transform: uppercase;
        }

        .indent {
            padding-left: 8px;
        }

        .tot-final {
            font-size: 12px;
            font-weight: 700;
        }

        .btnbar {
            display:flex;
            justify-content:space-between;
            gap:8px;
            width:76mm;
            max-width:76mm;
            margin:0 auto 8px;
        }

        .btn {
            padding:8px 10px;
            border:1px solid #ddd;
            background:#fff;
            border-radius:10px;
            font-weight:700;
            cursor:pointer;
            font-size:11px;
            text-decoration:none;
            color:#000;
            text-align:center;
        }

        @media print {
            .no-print { display:none !important; }
        }
    </style>
</head>
<body>

    <div id="btnbar" class="btnbar no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
        <a class="btn" href="{{ route('admin.caja.index') }}">Volver</a>
    </div>

    <div class="ticket">
        <div class="center bold">CIERRE DE CAJA</div>
        <div class="center">Turno #{{ $caja->turno }}</div>
        <div class="center">{{ optional($caja->fecha)->format('d/m/Y') }}</div>

        <div class="line"></div>

        <div class="row">
            <span>Caja ID</span>
            <span>#{{ $caja->id }}</span>
        </div>
        <div class="row">
            <span>Estado</span>
            <span>{{ strtoupper($caja->estado) }}</span>
        </div>
        <div class="row">
            <span>Apertura</span>
            <span>{{ optional($caja->abierta_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Cierre</span>
            <span>{{ optional($caja->cerrada_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Abrió</span>
            <span class="wrap">{{ $usuarioApertura->name ?? ('User #' . ($caja->abierta_por ?? '-')) }}</span>
        </div>
        <div class="row">
            <span>Cerró</span>
            <span class="wrap">{{ $usuarioCierre->name ?? ('User #' . ($caja->cerrada_por ?? '-')) }}</span>
        </div>

        @if(!empty($caja->observacion))
            <div class="mt-2">
                <div class="bold">Observación</div>
                <div class="wrap">{{ $caja->observacion }}</div>
            </div>
        @endif

        <div class="line"></div>

        <div class="section-title">Resumen</div>

        <div class="row">
            <span>Apertura</span>
            <span>$ {{ number_format((float)$caja->efectivo_apertura, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Otros ingresos</span>
            <span>$ {{ number_format((float)$otrosIngresos, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Propinas</span>
            <span>$ {{ number_format((float)$propinas, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Salidas</span>
            <span>$ {{ number_format((float)$otrasSalidas, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Ventas total</span>
            <span>$ {{ number_format((float)$ventasTotal, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Pagado total</span>
            <span>$ {{ number_format((float)$ventasPagadoTotal, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Vuelto</span>
            <span>$ {{ number_format((float)$ventasVuelto, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Efectivo bruto</span>
            <span>$ {{ number_format((float)$efectivoBruto, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Efectivo ventas neto</span>
            <span>$ {{ number_format((float)$efectivoVentasNeto, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Débito</span>
            <span>$ {{ number_format((float)$debitoTotal, 2, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Transferencia</span>
            <span>$ {{ number_format((float)$transferTotal, 2, ',', '.') }}</span>
        </div>

        <div class="line"></div>

        <div class="row tot-final">
            <span>Efectivo final</span>
            <span>$ {{ number_format((float)$caja->efectivo_turno, 2, ',', '.') }}</span>
        </div>

        <div class="line"></div>

        <div class="section-title">Ventas ({{ $ventas->count() }})</div>

        @forelse($ventas as $v)
            <div class="row">
                <span>Venta #{{ $v->id }}</span>
                <span>{{ optional($v->sold_at)->timezone('America/Argentina/Buenos_Aires')->format('H:i') }}</span>
            </div>

            <div class="row small">
                <span>Comanda #{{ $v->id_comanda }}</span>
                <span>$ {{ number_format((float)$v->total, 2, ',', '.') }}</span>
            </div>

            @if((float)($v->propina ?? 0) > 0)
                <div class="row small">
                    <span class="indent">Propina</span>
                    <span>$ {{ number_format((float)$v->propina, 2, ',', '.') }}</span>
                </div>
            @endif

            @php
                $pagosVenta = $pagosPorVenta->get($v->id, collect());
            @endphp

            @foreach($pagosVenta as $p)
                <div class="row small">
                    <span class="indent">{{ ucfirst($p->tipo) }}</span>
                    <span>$ {{ number_format((float)$p->monto, 2, ',', '.') }}</span>
                </div>
            @endforeach

            @if((float)$v->vuelto > 0)
                <div class="row small">
                    <span class="indent">Vuelto</span>
                    <span>$ {{ number_format((float)$v->vuelto, 2, ',', '.') }}</span>
                </div>
            @endif

            @if(!empty($v->nota))
                <div class="small wrap mt-1">
                    Nota: {{ $v->nota }}
                </div>
            @endif

            <div class="line"></div>
        @empty
            <div class="small">Sin ventas en este turno.</div>
            <div class="line"></div>
        @endforelse

        <div class="section-title">Movimientos</div>

        @forelse($movimientos as $m)
            <div class="row">
                <span>{{ optional($m->movido_at)->timezone('America/Argentina/Buenos_Aires')->format('H:i') }} · {{ strtoupper($m->tipo) }}</span>
                <span>$ {{ number_format((float)$m->monto, 2, ',', '.') }}</span>
            </div>

            <div class="small wrap">
                {{ $m->concepto ?: 'Sin concepto' }}
            </div>

            <div class="line"></div>
        @empty
            <div class="small">Sin movimientos registrados.</div>
            <div class="line"></div>
        @endforelse

        <div class="center mt-3">*** FIN DEL TURNO ***</div>
    </div>

    <script>
    (function () {
        try {
            if (window.top && window !== window.top) {
                const bar = document.getElementById('btnbar');
                if (bar) bar.style.display = 'none';
            }
        } catch (e) {}

        let notified = false;

        function notifyParent() {
            if (notified) return;
            notified = true;

            try {
                window.parent.postMessage({
                    type: 'RF_PRINT_DONE',
                    mode: 'turno',
                    turno_id: {{ (int)$caja->id }}
                }, '*');
            } catch (e) {}
        }

        window.addEventListener('afterprint', function () {
            notifyParent();
        });

        setTimeout(function () {
            notifyParent();
        }, 3000);
    })();
    </script>
</body>
</html>