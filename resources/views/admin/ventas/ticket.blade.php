<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ticket - Venta #{{ $venta->id }}</title>
    <style>
        body{ font-family: Arial, sans-serif; margin:0; padding:16px; background:#f8fafc; }
        .wrap{ max-width:420px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; }
        .head{ padding:16px 18px; border-bottom:1px dashed #e5e7eb; }
        .body{ padding:14px 18px; }
        .muted{ color:#64748b; font-size:12px; }
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        .title{ font-size:20px; font-weight:900; }
        .subtitle{ font-size:18px; font-weight:900; margin-top:2px; }
        table{ width:100%; border-collapse:collapse; margin-top:10px; }
        th,td{ padding:6px 0; border-bottom:1px dashed #e5e7eb; font-size:13px; vertical-align:top; }
        th{ text-align:left; font-weight:900; color:#0f172a; }
        .right{ text-align:right; }
        .total{ font-size:18px; font-weight:900; }
        .btnbar{ display:flex; justify-content:space-between; gap:8px; max-width:420px; margin:0 auto 10px; }
        .btn{ padding:10px 12px; border-radius:12px; border:1px solid #e5e7eb; background:#fff; font-weight:900; cursor:pointer; }
        .btnPrimary{ background:#0f172a; color:#fff; border-color:#0f172a; }
        .hr{ border-top:1px dashed #e5e7eb; margin:12px 0; }
        @media print{
            .no-print{ display:none !important; }
            body{ padding:0; background:#fff !important; }
            .wrap{ border:none !important; border-radius:0 !important; }
            .head{ border-bottom:1px dashed #000 !important; }
            th,td{ border-bottom:1px dashed #000 !important; }
        }
    </style>
</head>
<body>
@php
    $back = request('back');
    $soldAt = $venta->sold_at ? \Carbon\Carbon::parse($venta->sold_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i');

    $mesaNombre = $venta->mesa->nombre ?? ($venta->comanda->mesa->nombre ?? null);
    $mozoNombre = $venta->mozo->name ?? ($venta->comanda->mozo->name ?? null);

    $comandaId = $venta->id_comanda ?? ($venta->comanda->id ?? null);

    $items = $venta->comanda?->items?->where('estado','!=','anulado') ?? collect();
@endphp

<div class="no-print btnbar">
    <button class="btn btnPrimary" onclick="window.print()">🖨️ Imprimir</button>

    @if($back)
        <a class="btn" href="{{ $back }}" style="text-decoration:none; color:#0f172a;">Volver</a>
    @else
        <a class="btn" href="{{ route('admin.caja.index') }}" style="text-decoration:none; color:#0f172a;">Volver</a>
    @endif
</div>

<div class="wrap">
    <div class="head">
        <div class="muted">Ticket</div>
        <div class="mono title">LA PISCALA</div>
        <div class="mono subtitle">VENTA #{{ $venta->id }}</div>

        <div style="margin-top:10px; font-size:13px;">
            <div class="muted">Fecha: {{ $soldAt }}</div>
            @if($comandaId)
                <div><b>Comanda:</b> #{{ $comandaId }}</div>
            @endif
            @if($mesaNombre)
                <div><b>Mesa:</b> {{ $mesaNombre }}</div>
            @endif
            @if($mozoNombre)
                <div><b>Mozo:</b> {{ $mozoNombre }}</div>
            @endif
        </div>
    </div>

    <div class="body">

        {{-- ✅ Detalle de items (si existe comanda cargada) --}}
        @if($items->count())
            <div class="muted" style="font-weight:900; margin-bottom:6px;">DETALLE</div>
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th class="right">Cant</th>
                    <th class="right">Imp</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    @php
                        $imp = (float)$it->precio_snapshot * (float)$it->cantidad;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:900;">{{ $it->nombre_snapshot }}</div>
                            @if(!empty($it->nota))
                                <div class="muted">{{ $it->nota }}</div>
                            @endif
                        </td>
                        <td class="right">{{ rtrim(rtrim(number_format((float)$it->cantidad, 2, '.', ''), '0'), '.') }}</td>
                        <td class="right">$ {{ number_format($imp, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="hr"></div>
        @endif

        {{-- Totales --}}
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">$ {{ number_format((float)($venta->subtotal ?? 0), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Descuento</td>
                <td class="right">- $ {{ number_format((float)($venta->descuento ?? 0), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Recargo</td>
                <td class="right">+ $ {{ number_format((float)($venta->recargo ?? 0), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="font-weight:900;">TOTAL</td>
                <td class="right total">$ {{ number_format((float)($venta->total ?? 0), 2, ',', '.') }}</td>
            </tr>
        </table>

        {{-- Pagos --}}
        <div style="margin-top:12px;">
            <div class="muted" style="font-weight:900; margin-bottom:6px;">PAGOS</div>

            @if(($venta->pagos ?? collect())->count())
                <table>
                    @foreach($venta->pagos as $p)
                        <tr>
                            <td>
                                {{ strtoupper($p->tipo) }}
                                @if(!empty($p->referencia))
                                    <div class="muted">{{ $p->referencia }}</div>
                                @endif
                            </td>
                            <td class="right">$ {{ number_format((float)$p->monto, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="muted">Sin detalle de pagos.</div>
            @endif
        </div>

        <div style="margin-top:10px;">
            <table>
                <tr>
                    <td>Pagado</td>
                    <td class="right">$ {{ number_format((float)($venta->pagado_total ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Vuelto</td>
                    <td class="right">$ {{ number_format((float)($venta->vuelto ?? 0), 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if(!empty($venta->nota))
            <div style="margin-top:12px;">
                <div class="muted" style="font-weight:900;">NOTA</div>
                <div class="mono">{{ $venta->nota }}</div>
            </div>
        @endif

        <div class="muted" style="margin-top:12px;">
            Gracias por su compra.
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