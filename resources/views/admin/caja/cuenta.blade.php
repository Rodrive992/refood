{{-- resources/views/admin/caja/cuenta.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cuenta - Comanda #{{ $comanda->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc; 
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .wrap { 
            max-width: 420px; 
            width: 100%;
            margin: 0 auto; 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .head { 
            padding: 20px; 
            border-bottom: 1px dashed #e2e8f0; 
            background: linear-gradient(135deg, #f8fafc 0%, white 100%);
        }
        .body { padding: 20px; }
        .muted { color: #64748b; font-size: 12px; }
        .mono { font-family: 'Courier New', monospace; }
        .title { font-size: 24px; font-weight: 800; color: #0f172a; }
        .subtitle { font-size: 18px; font-weight: 700; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 14px; vertical-align: top; }
        th { color: #475569; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .right { text-align: right; }
        .total { font-size: 20px; font-weight: 800; color: #0f172a; }
        .btnbar { 
            max-width: 420px; 
            width: 100%;
            margin: 0 auto 16px; 
            display: flex; 
            gap: 12px; 
            justify-content: flex-end;
        }
        .btn { 
            padding: 10px 20px; 
            border-radius: 10px; 
            border: 1px solid #e2e8f0; 
            background: white; 
            font-weight: 600; 
            font-size: 14px;
            cursor: pointer; 
            text-decoration: none;
            color: #334155;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover { background: #f8fafc; }
        .btnPrimary { 
            background: #0f172a; 
            color: white; 
            border-color: #0f172a; 
        }
        .btnPrimary:hover { background: #1e293b; }
        .item-note { font-size: 12px; color: #64748b; margin-top: 2px; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .wrap { border: none !important; box-shadow: none; }
            .head { background: white; }
        }
    </style>
</head>
<body>

    <div class="no-print btnbar">
        <button class="btn btnPrimary" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir
        </button>
        <a class="btn" href="{{ route('admin.caja.show', $comanda) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al cobro
        </a>
    </div>

    @php
        $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
        $mozoNombre = $comanda->mozo->name ?? ('#'.$comanda->id_mozo);
        $sol = $comanda->cuenta_solicitada_at
            ? \Carbon\Carbon::parse($comanda->cuenta_solicitada_at)->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i')
            : '—';
        $impreso = now()->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i');
    @endphp

    <div class="wrap">
        <div class="head">
            <div class="muted">Pre-ticket</div>
            <div class="title">LA PISCALA</div>
            <div class="subtitle mono">COMANDA #{{ $comanda->id }}</div>

            <div style="margin-top: 16px; display: grid; gap: 6px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Mesa:</span>
                    <span style="font-weight: 600;">{{ $mesaNombre }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Mozo:</span>
                    <span style="font-weight: 600;">{{ $mozoNombre }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Solicitada:</span>
                    <span style="font-weight: 600;">{{ $sol }}</span>
                </div>
                @if(!empty($comanda->cuenta_solicitada_nota))
                    <div style="display: flex; justify-content: space-between;">
                        <span class="muted">Nota:</span>
                        <span style="font-style: italic;">{{ $comanda->cuenta_solicitada_nota }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="body">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="right">Cant</th>
                        <th class="right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comanda->items->where('estado','!=','anulado') as $it)
                        @php $imp = (float)$it->precio_snapshot * (float)$it->cantidad; @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $it->nombre_snapshot }}</div>
                                @if(!empty($it->nota))
                                    <div class="item-note">{{ $it->nota }}</div>
                                @endif
                            </td>
                            <td class="right">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</td>
                            <td class="right" style="font-weight: 600;">${{ number_format($imp, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span class="muted">Total estimado</span>
                <span class="total">${{ number_format((float)$subtotal, 0, ',', '.') }}</span>
            </div>

            <div style="margin-top: 20px; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
                <div class="muted" style="text-align: center;">
                    * Este comprobante no es factura. Sujeto a cambios hasta el cobro.
                </div>
                <div class="muted" style="text-align: center; margin-top: 6px;">
                    Impreso: {{ $impreso }}
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Notificar al padre si estamos en iframe
            try{
                if (window.top && window !== window.top) {
                    setTimeout(function() {
                        window.top.postMessage({
                            type: 'RF_PRINT_DONE',
                            mode: 'preticket',
                            comanda_id: {{ (int)$comanda->id }},
                        }, '*');
                    }, 500);
                }
            }catch(e){}
        })();
    </script>
</body>
</html>