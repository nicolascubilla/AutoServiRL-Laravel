@php
$ncot              = '001';
$cliente_nombre    = 'NOMBRE DEL CLIENTE';
$cliente_ruc       = '';
$cliente_direccion = '';

$validez_dias      = 15;
$fecha             = date('d/m/Y');

$precio_a        = 10000000;
$precio_b        = 14000000;
$precio_c_base   = 14000000;
$cuota_mensual   = 1000000;

$negocio_nombre    = $configuracion['nombre_negocio'] ?? 'AUTOSERVICES R.&L.';
$negocio_actividad = $configuracion['actividad'] ?? '';
$negocio_ruc       = $configuracion['ruc'] ?? '';
$negocio_direccion = $configuracion['direccion'] ?? '';
$negocio_ciudad    = $configuracion['ciudad'] ?? '';
$negocio_telefono  = $configuracion['telefono'] ?? '';

function gs($n) {
    return 'Gs. ' . number_format($n, 0, ',', '.');
}
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $ncot }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #fff;
            padding: 36px;
            font-size: 13px;
            line-height: 1.5;
        }
        .print-btn {
            position: fixed; top: 16px; right: 16px;
            background: #2563eb; color: #fff; border: none;
            border-radius: 8px; padding: 10px 18px;
            font-size: 14px; cursor: pointer;
        }
        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .brand-box { display: flex; align-items: center; gap: 16px; }
        .logo {
            width: 62px; height: 62px; border-radius: 12px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 30px; font-weight: 800; flex-shrink: 0;
        }
        .brand-name { font-size: 23px; font-weight: 800; color: #15803d; }
        .brand-activity { color: #6b7280; font-size: 12px; max-width: 380px; }
        .brand-contact { color: #6b7280; font-size: 12px; margin-top: 4px; }
        .cotiz-title {
            text-align: right; background: #16a34a; color: #fff;
            padding: 12px 22px; border-radius: 8px;
        }
        .cotiz-title .t { font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .cotiz-title .n { font-size: 14px; margin-top: 2px; }

        .ref-table { width: 100%; border-collapse: collapse; margin-top: 26px; }
        .ref-table td { border: 1px solid #e5e7eb; padding: 10px 12px; vertical-align: top; }
        .ref-table .lbl { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; }
        .ref-table .half { width: 50%; }
        .ref-table strong { font-size: 13px; }

        h3.section {
            font-size: 15px; margin: 26px 0 12px; padding-bottom: 6px;
            border-bottom: 2px solid #16a34a; font-weight: 700;
        }
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th {
            background: #f3f4f6; border: 1px solid #e5e7eb;
            padding: 10px; text-align: left; font-size: 12px;
        }
        table.items td { border: 1px solid #e5e7eb; padding: 12px; vertical-align: top; }
        .item-name { font-weight: 700; font-size: 13px; }
        .item-desc { color: #4b5563; font-size: 12px; margin-top: 4px; list-style: disc; padding-left: 16px; }
        .item-desc li { margin-top: 2px; }
        .price { text-align: right; white-space: nowrap; font-weight: 700; }

        .notes { margin-top: 24px; }
        .notes h4 { font-size: 13px; margin-bottom: 8px; }
        .notes ul { list-style: none; padding-left: 0; }
        .notes li { position: relative; padding-left: 20px; margin-bottom: 5px; font-size: 12px; }
        .notes li:before { content: "✓"; color: #16a34a; position: absolute; left: 0; font-weight: 700; }

        .sign { display: flex; gap: 60px; margin-top: 56px; }
        .sign .box { flex: 1; text-align: center; }
        .sign .line { border-top: 1px solid #111827; margin-top: 56px; padding-top: 8px; font-size: 12px; font-weight: 700; }

        .foot { text-align: center; color: #6b7280; font-size: 11px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 12px; }

        @media print {
            .print-btn { display: none; }
            body { padding: 14px; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

    <div class="head">
        <div>
            <div class="brand-box">
                <div class="logo">A</div>
                <div>
                    <div class="brand-name">{{ $negocio_nombre }}</div>
                    <div class="brand-activity">{{ $negocio_actividad }}</div>
                </div>
            </div>
            <div class="brand-contact">
                {{ $negocio_direccion }} {{ $negocio_ciudad }}<br>
                Tel: {{ $negocio_telefono }} &nbsp;|&nbsp; RUC: {{ $negocio_ruc }}
            </div>
        </div>
        <div class="cotiz-title">
            <div class="t">COTIZACIÓN</div>
            <div class="n">Nº {{ $ncot }}</div>
        </div>
    </div>

    <table class="ref-table">
        <tr>
            <td class="half">
                <div class="lbl">Señor(es)</div>
                <strong>{{ $cliente_nombre }}</strong>
                @if ($cliente_ruc !== '')<br><span style="font-size:12px;">RUC: {{ $cliente_ruc }}</span>@endif
                @if ($cliente_direccion !== '')<br><span style="font-size:12px;">{{ $cliente_direccion }}</span>@endif
            </td>
            <td class="half">
                <div class="lbl">Fecha de emisión</div>
                <strong>{{ $fecha }}</strong>
                <div class="lbl" style="margin-top:10px;">Validez de la oferta</div>
                <strong>{{ (int)$validez_dias }} días</strong>
            </td>
        </tr>
    </table>

    <h3 class="section">Propuesta de Sistema de Gestión y Facturación</h3>
    <table class="items">
        <thead>
            <tr>
                <th style="width:36%;">Descripción</th>
                <th style="width:8%;">Paquete</th>
                <th>Incluye</th>
                <th style="width:15%;" class="price">Precio</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="item-name">Software llave en mano</div>
                </td>
                <td><strong>A</strong></td>
                <td>
                    <ul class="item-desc">
                        <li>Punto de venta (PDV) con lector de código de barras</li>
                        <li>Gestión de productos y control de stock</li>
                        <li>Apertura y cierre de caja con control de efectivo</li>
                        <li>Facturación fiscal (IVA 5%/10%/exento, timbrado, numeración)</li>
                        <li>Búsqueda de cliente por RUC (conexión SET)</li>
                        <li>Informes (ventas, facturación, top productos) con exportación PDF/Excel</li>
                        <li>Base de datos PostgreSQL + instalación en su equipo</li>
                        <li>Configuración inicial del negocio</li>
                    </ul>
                </td>
                <td class="price">{{ gs($precio_a) }}</td>
            </tr>
            <tr>
                <td>
                    <div class="item-name">Implementación completa</div>
                    <div class="item-desc" style="list-style:none;padding-left:0;">Todo el paquete A, más:</div>
                </td>
                <td><strong>B</strong></td>
                <td>
                    <ul class="item-desc">
                        <li>Instalación en el local del cliente</li>
                        <li>Capacitación del personal (carga de productos, ventas, cierre de caja, facturación)</li>
                        <li>1 mes de soporte incluido</li>
                    </ul>
                </td>
                <td class="price">{{ gs($precio_b) }}</td>
            </tr>
            <tr>
                <td>
                    <div class="item-name">Con soporte mensual</div>
                    <div class="item-desc" style="list-style:none;padding-left:0;">Todo el paquete B, más servicio continuo:</div>
                </td>
                <td><strong>C</strong></td>
                <td>
                    <ul class="item-desc">
                        <li>Actualizaciones y mejoras del sistema</li>
                        <li>Soporte técnico y mantenimiento</li>
                        <li>Respaldo y custodia de la base de datos</li>
                        <li>Cuota mensual (contrato de soporte)</li>
                    </ul>
                </td>
                <td class="price">
                    {{ gs($precio_c_base) }}<br>
                    <span style="font-weight:400;font-size:11px;color:#4b5563;">+ {{ gs($cuota_mensual) }}/mes</span>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="notes">
        <h4>Condiciones comerciales</h4>
        <ul>
            <li>Los precios incluyen los impuestos correspondientes.</li>
            <li>Forma de pago: 50% de anticipo para iniciar la implementación y 50% contra entrega / puesta en marcha.</li>
            <li>La oferta tiene una validez de <strong>{{ (int)$validez_dias }} días</strong>.</li>
            <li>Cada punto de venta (terminal) adicional se cotiza por separado.</li>
            <li>La garantía cubre defectos de funcionamiento por un período de 30 días.</li>
            <li>Requisitos técnicos del cliente: computadora con internet para la consulta de RUC y servidor local de base de datos.</li>
        </ul>
    </div>

    <div class="sign">
        <div class="box"><div class="line">Firma del Vendedor</div></div>
        <div class="box"><div class="line">Firma del Cliente</div></div>
    </div>

    <div class="foot">
        {{ $negocio_nombre }} - Sistema de Gestión y Facturación. Gracias por su preferencia.
    </div>

</body>
</html>
