@php
$informeVentas = $informeVentas ?? (object)['cantidad' => 0, 'total' => 0, 'efectivo' => 0, 'transferencia' => 0];
$ventasPorDia = $ventasPorDia ?? collect();
$informeFacturacion = $informeFacturacion ?? (object)['cantidad' => 0, 'total' => 0, 'total_iva' => 0, 'total_exenta' => 0];
$topProductos = $topProductos ?? collect();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Ventas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #fff;
            padding: 32px;
            font-size: 13px;
        }
        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 14px;
            cursor: pointer;
        }
        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 16px;
        }
        .brand { font-size: 22px; font-weight: 800; color: #15803d; }
        .head-left { line-height: 1.5; }
        .head-left .muted { color: #6b7280; font-size: 12px; }
        .head-right { text-align: right; }
        .head-right .badge { background: #16a34a; color: #fff; padding: 6px 14px; border-radius: 6px; font-weight: 700; }
        .title {
            text-align: center;
            margin: 20px 0 8px;
            font-size: 18px;
            font-weight: 700;
        }
        .period {
            text-align: center;
            color: #6b7280;
            margin-bottom: 22px;
            font-size: 13px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            background: #f9fafb;
        }
        .card .lbl { color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .card .val { font-size: 16px; font-weight: 700; margin-top: 4px; }
        .sec-title {
            font-size: 14px;
            font-weight: 700;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; }
        th { background: #f3f4f6; font-size: 12px; }
        td { font-size: 12px; }
        .num { text-align: right; }
        .center { text-align: center; }
        .total-row td { font-weight: 700; background: #f0fdf4; }
        .foot {
            margin-top: 28px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">
        <i></i> Imprimir / Guardar PDF
    </button>

    <div class="head">
        <div class="head-left">
            <div class="brand">{{ $configuracion->nombre_negocio ?? 'AutoServiRL' }}</div>
            <div class="muted">{{ $configuracion->actividad ?? '' }}</div>
            <div class="muted">{{ $configuracion->direccion ?? '' }} - {{ $configuracion->ciudad ?? '' }}</div>
            <div class="muted">Tel: {{ $configuracion->telefono ?? '-' }} | RUC: {{ $configuracion->ruc ?? '-' }}</div>
        </div>
        <div class="head-right">
            <div class="badge">REPORTE</div>
            <div class="muted" style="margin-top:6px;">Generado: {{ date('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="title">Informe de Ventas y Facturación</div>
    <div class="period">
        Período: {{ date('d/m/Y', strtotime($desde)) }} al {{ date('d/m/Y', strtotime($hasta)) }}
    </div>

    <div class="cards">
        <div class="card">
            <div class="lbl">Ventas</div>
            <div class="val">{{ $informeVentas->cantidad }}</div>
        </div>
        <div class="card">
            <div class="lbl">Total vendido</div>
            <div class="val">Gs. {{ number_format($informeVentas->total, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="lbl">Efectivo</div>
            <div class="val">Gs. {{ number_format($informeVentas->efectivo, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="lbl">Transferencia</div>
            <div class="val">Gs. {{ number_format($informeVentas->transferencia, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="sec-title">Ventas por día</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="center">Ventas</th>
                <th class="num">Total (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventasPorDia as $d)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($d->fecha)) }}</td>
                    <td class="center">{{ (int)$d->cantidad }}</td>
                    <td class="num">{{ number_format($d->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="center">{{ $informeVentas->cantidad }}</td>
                <td class="num">{{ number_format($informeVentas->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sec-title">Resumen de facturación</div>
    <table>
        <thead>
            <tr>
                <th>Facturas emitidas</th>
                <th class="num">Total facturado (Gs.)</th>
                <th class="num">IVA (Gs.)</th>
                <th class="num">Base exenta (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $informeFacturacion->cantidad }}</td>
                <td class="num">{{ number_format($informeFacturacion->total, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($informeFacturacion->total_iva, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($informeFacturacion->total_exenta, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sec-title">Productos más vendidos</div>
    <table>
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Producto</th>
                <th class="center">Cantidad</th>
                <th class="num">Total (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topProductos as $i => $p)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $p->descripcion }}</td>
                    <td class="center">{{ rtrim(rtrim(number_format($p->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                    <td class="num">{{ number_format($p->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="foot">
        Sistema AutoServiRL - Reporte generado automáticamente.
    </div>

</body>
</html>
