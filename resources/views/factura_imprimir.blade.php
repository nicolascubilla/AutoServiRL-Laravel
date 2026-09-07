@php
$numeroFactura =
    str_pad($factura->establecimiento ?? $configuracion->establecimiento ?? '001', 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($factura->punto_expedicion ?? $configuracion->punto_expedicion ?? '001', 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($factura->numero_factura ?? 0, 7, '0', STR_PAD_LEFT);

$fechaEmision = '';
if (!empty($factura->fecha_emision)) {
    $fechaEmision = date('d/m/Y H:i', strtotime($factura->fecha_emision));
}
$fechaInicioVigencia = '';
if (!empty($configuracion->fecha_inicio_vigencia)) {
    $fechaInicioVigencia = date('d/m/Y', strtotime($configuracion->fecha_inicio_vigencia));
}
$fechaFinVigencia = '';
if (!empty($configuracion->fecha_fin_vigencia)) {
    $fechaFinVigencia = date('d/m/Y', strtotime($configuracion->fecha_fin_vigencia));
}

$totalItems = count($detalle);
$totalCantidad = 0;
foreach ($detalle as $item) {
    $totalCantidad += (float)($item->cantidad ?? 0);
}
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $numeroFactura }}</title>

    <style>
        :root {
            --brand: #16a34a;
            --brand-dark: #15803d;
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --soft: #f3f4f6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 12px;
            color: var(--ink);
            background: var(--soft);
            padding: 24px;
        }

        .hoja {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            padding: 32px 36px;
            overflow: hidden;
        }

        .barra {
            height: 6px;
            background: linear-gradient(90deg, var(--brand), #22c55e);
            margin: -32px -36px 28px;
        }

        .encabezado {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            align-items: start;
        }

        .datos-negocio {
            text-align: center;
        }

        .logo-marca {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--brand);
            color: #fff;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .nombre-negocio {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--brand-dark);
            line-height: 1.2;
        }

        .actividad {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            margin-top: 3px;
        }

        .propietario {
            font-size: 11px;
            font-style: italic;
            color: var(--muted);
            margin-top: 4px;
        }

        .direccion,
        .telefono,
        .ciudad {
            font-size: 11px;
            color: var(--ink);
            margin-top: 2px;
        }

        .rotulo {
            border: 2px solid var(--brand);
            border-radius: 12px;
            text-align: center;
            padding: 14px 16px;
            background: linear-gradient(180deg, #f0fdf4, #fff);
        }

        .rotulo .titulo {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 6px;
            color: var(--brand-dark);
        }

        .rotulo .numero {
            font-size: 20px;
            font-weight: 700;
            margin-top: 6px;
            font-variant-numeric: tabular-nums;
        }

        .datos-legales {
            margin-top: 14px;
            font-size: 10.5px;
            line-height: 1.7;
        }

        .datos-legales strong {
            color: var(--ink);
        }

        .fila-emision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
            margin-top: 22px;
        }

        .campo {
            display: flex;
            align-items: center;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 8px 12px;
        }

        .campo .etiqueta {
            font-weight: 700;
            color: var(--muted);
            margin-right: 8px;
            white-space: nowrap;
        }

        .campo .valor {
            font-weight: 600;
        }

        .divisor {
            border: none;
            border-top: 2px solid var(--brand);
            margin: 22px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-detalle {
            margin-top: 6px;
        }

        .tabla-detalle thead th {
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            padding: 9px 10px;
            text-align: center;
            border: 1px solid var(--brand-dark);
        }

        .tabla-detalle thead th:nth-child(2) {
            text-align: left;
        }

        .tabla-detalle tbody td {
            border: 1px solid var(--line);
            padding: 8px 10px;
            text-align: center;
        }

        .tabla-detalle tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .tabla-detalle tbody td:nth-child(2) {
            text-align: left;
        }

        .derecha {
            text-align: right !important;
        }

        .total-filas td {
            font-weight: 700;
            background: var(--soft);
        }

        .zona-totales {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
            margin-top: 22px;
            align-items: end;
        }

        .liquidacion h4 {
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--muted);
        }

        .tabla-iva td,
        .tabla-iva th {
            border: 1px solid var(--line);
            padding: 7px 10px;
        }

        .tabla-iva thead th {
            background: var(--soft);
            font-size: 11px;
        }

        .tarjeta-total {
            border: 2px solid var(--brand);
            border-radius: 12px;
            overflow: hidden;
        }

        .tarjeta-total .caja-subtotal {
            display: flex;
            justify-content: space-between;
            padding: 10px 16px;
            border-bottom: 1px dashed var(--line);
        }

        .tarjeta-total .caja-grande {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--brand);
            color: #fff;
            padding: 14px 16px;
        }

        .caja-grande .lbl {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .caja-grande .monto {
            font-size: 20px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        .pie {
            margin-top: 28px;
            text-align: center;
            font-size: 10.5px;
            color: var(--muted);
            border-top: 1px solid var(--line);
            padding-top: 14px;
            line-height: 1.8;
        }

        .pie .gracias {
            font-size: 12px;
            font-weight: 700;
            color: var(--brand-dark);
        }

        .botones {
            margin-top: 26px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .botones button {
            border: none;
            padding: 11px 26px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: .15s;
        }

        .btn-imprimir {
            background: var(--brand);
            color: #fff;
        }

        .btn-imprimir:hover {
            background: var(--brand-dark);
        }

        .btn-cerrar {
            background: var(--soft);
            color: var(--ink);
            border: 1px solid var(--line) !important;
        }

        .btn-cerrar:hover {
            background: #e5e7eb;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                font-size: 11px;
            }

            .hoja {
                max-width: none;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }

            .barra {
                margin: 0;
                margin-bottom: 16px;
            }

            .botones {
                display: none;
            }

            .tabla-detalle thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .caja-grande {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="hoja">

        <div class="barra"></div>

        <div class="encabezado">

            <div class="datos-negocio">
                <div class="logo-marca">{{ mb_substr($configuracion->nombre_negocio ?? 'AS', 0, 1) }}</div>
                <div class="nombre-negocio">{{ $configuracion->nombre_negocio ?? '' }}</div>
                <div class="actividad">{{ $configuracion->actividad ?? '' }}</div>
                <div class="propietario">{{ $configuracion->propietario ?? '' }}</div>
                <div class="direccion">{{ $configuracion->direccion ?? '' }}</div>
                <div class="telefono">Tel.: {{ $configuracion->telefono ?? '' }} &mdash; {{ $configuracion->ciudad ?? '' }}</div>
            </div>

            <div class="rotulo">
                <div class="titulo">FACTURA</div>
                <div class="numero">N.º {{ $numeroFactura }}</div>
                <div class="datos-legales">
                    <div><strong>Timbrado N.º:</strong> {{ $configuracion->timbrado ?? '' }}</div>
                    <div><strong>Vigencia:</strong> {{ $fechaInicioVigencia }} al {{ $fechaFinVigencia }}</div>
                    <div><strong>RUC:</strong> {{ $configuracion->ruc ?? '' }}</div>
                </div>
            </div>

        </div>

        <div class="fila-emision">
            <div class="campo">
                <span class="etiqueta">Fecha emisión:</span>
                <span class="valor">{{ $fechaEmision ?: '-' }}</span>
            </div>
            <div class="campo">
                <span class="etiqueta">Condición:</span>
                <span class="valor">CONTADO</span>
            </div>
        </div>

        <div class="fila-emision">
            <div class="campo">
                <span class="etiqueta">Cliente:</span>
                <span class="valor">{{ $factura->cliente_nombre ?? '' }}</span>
            </div>
            <div class="campo">
                <span class="etiqueta">RUC / C.I.:</span>
                <span class="valor">{{ $factura->cliente_documento ?? '' }}</span>
            </div>
        </div>

        <hr class="divisor">

        <table class="tabla-detalle">
            <thead>
                <tr>
                    <th style="width:7%">Cant.</th>
                    <th>Descripción</th>
                    <th style="width:14%">Precio Unit.</th>
                    <th style="width:13%">Exentas</th>
                    <th style="width:13%">Grav. 5%</th>
                    <th style="width:13%">Grav. 10%</th>
                    <th style="width:15%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalle as $item)
                    <tr>
                        <td>{{ number_format($item->cantidad ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $item->descripcion ?? '' }}</td>
                        <td class="derecha">Gs. {{ number_format($item->precio_unitario ?? 0, 0, ',', '.') }}</td>
                        <td class="derecha">{{ number_format($item->exenta ?? 0, 0, ',', '.') }}</td>
                        <td class="derecha">{{ number_format($item->gravada_5 ?? 0, 0, ',', '.') }}</td>
                        <td class="derecha">{{ number_format($item->gravada_10 ?? 0, 0, ',', '.') }}</td>
                        <td class="derecha"><strong>Gs. {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="zona-totales">

            <div class="liquidacion">
                <h4>Liquidación del IVA</h4>
                <table class="tabla-iva">
                    <thead>
                        <tr>
                            <th>I.V.A. 5%</th>
                            <th>I.V.A. 10%</th>
                            <th>Total I.V.A.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="derecha">Gs. {{ number_format($factura->total_iva_5 ?? 0, 0, ',', '.') }}</td>
                            <td class="derecha">Gs. {{ number_format($factura->total_iva_10 ?? 0, 0, ',', '.') }}</td>
                            <td class="derecha">Gs. {{ number_format($factura->total_iva ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="campo" style="margin-top:14px">
                    <span class="etiqueta">Artículos:</span>
                    <span class="valor">{{ $totalItems }}</span>
                    <span style="margin:0 14px"></span>
                    <span class="etiqueta">Unidades:</span>
                    <span class="valor">{{ number_format($totalCantidad, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="tarjeta-total">
                <div class="caja-subtotal">
                    <span class="etiqueta" style="font-weight:700">Subtotal</span>
                    <strong>Gs. {{ number_format($factura->total ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="caja-grande">
                    <span class="lbl">Total a Pagar</span>
                    <span class="monto">Gs. {{ number_format($factura->total ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        <div class="pie">
            <div class="gracias">¡Gracias por su compra!</div>
            Documento emitido por el sistema AutoServiRL
        </div>

        <div class="botones">
            <button type="button" class="btn-imprimir" onclick="window.print()">🖨 Imprimir</button>
            <button type="button" class="btn-cerrar" onclick="window.close()">Cerrar</button>
        </div>

    </div>

</body>

</html>
