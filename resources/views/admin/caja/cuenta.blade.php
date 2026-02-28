<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cuenta - Comanda #{{ $comanda->id }}</title>
    <style>
        body{ font-family: Arial, sans-serif; margin:0; padding:16px; background:#f8fafc; }
        .wrap{ max-width:420px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; }
        .head{ padding:16px 18px; border-bottom:1px dashed #e5e7eb; }
        .body{ padding:14px 18px; }
        .muted{ color:#64748b; font-size:12px; }
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        table{ width:100%; border-collapse:collapse; margin-top:10px; }
        th,td{ text-align:left; padding:6px 0; border-bottom:1px dashed #e5e7eb; font-size:13px; vertical-align:top; }
        .right{ text-align:right; }
        .total{ font-size:18px; font-weight:800; }
        .btnbar{ display:flex; justify-content:space-between; gap:8px; max-width:420px; margin:0 auto 10px; }
        .btn{ padding:10px 12px; border-radius:12px; border:1px solid #e5e7eb; background:#fff; font-weight:800; cursor:pointer; }
        .btnPrimary{ background:#0f172a; color:#fff; border-color:#0f172a; }
        @media print{
            .no-print{ display:none !important; }
            body{ padding:0; background:#fff !important; }
            .wrap{ border:none !important; border-radius:0 !important; }
            .head{ border-bottom:1px dashed #000 !important; }
        }
    </style>
</head>
<body>

    <div class="no-print btnbar">
        <button class="btn btnPrimary" onclick="window.print()">🖨️ Imprimir</button>
        <a class="btn" href="{{ route('admin.caja.index') }}" style="text-decoration:none; color:#0f172a;">Volver</a>
    </div>

    @php
        $mesaNombre = $comanda->mesa->nombre ?? 'Sin mesa';
        $mozoNombre = $comanda->mozo->name ?? ('#'.$comanda->id_mozo);
        $sol = optional($comanda->cuenta_solicitada_at)->format('d/m/Y H:i');
    @endphp

    <div class="wrap">
        <div class="head">
            <div class="muted">Cuenta (pre-ticket)</div>
            <div class="mono" style="font-size:20px; font-weight:900; margin-top:2px;">LA PISCALA</div>
            <div class="mono" style="font-size:18px; font-weight:900;">COMANDA #{{ $comanda->id }}</div>

            <div style="margin-top:10px; font-size:13px;">
                <div><b>Mesa:</b> {{ $mesaNombre }}</div>
                <div><b>Mozo:</b> {{ $mozoNombre }}</div>
                <div class="muted">Solicitada: {{ $sol }}</div>
                @if(!empty($comanda->cuenta_solicitada_nota))
                    <div class="muted">Nota: {{ $comanda->cuenta_solicitada_nota }}</div>
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
                            <div style="font-weight:800;">{{ $it->nombre_snapshot }}</div>
                            @if(!empty($it->nota))
                                <div class="muted">{{ $it->nota }}</div>
                            @endif
                        </td>
                        <td class="right">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</td>
                        <td class="right">$ {{ number_format($imp, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                <div class="muted">Total estimado</div>
                <div class="total">$ {{ number_format((float)$subtotal, 0, ',', '.') }}</div>
            </div>

            <div class="muted" style="margin-top:14px;">
                * Este comprobante no es factura. Sujeto a cambios hasta el cobro.
            </div>

            <div class="muted" style="margin-top:10px;">
                Impreso: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        // auto print suave
        window.addEventListener('load', function(){
            setTimeout(() => { try{ window.print(); }catch(e){} }, 350);
        });
    </script>
</body>
</html>