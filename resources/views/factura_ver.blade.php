@extends('layouts.app')

@section('title', 'Factura | AutoServiRL')

@section('content')
@php
$numeroFactura =
    str_pad($factura->establecimiento ?? $configuracion->establecimiento ?? '001', 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($factura->punto_expedicion ?? $configuracion->punto_expedicion ?? '001', 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($factura->numero_factura ?? 0, 7, '0', STR_PAD_LEFT);

$fechaEmision = '';
if (!empty($factura->fecha_emision)) {
    $fechaEmision = date('d/m/Y H:i', strtotime($factura->fecha_emision));
}
$totalCantidad = 0;
foreach ($detalle as $item) { $totalCantidad += (float)($item->cantidad ?? 0); }
@endphp

<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Factura
                <span class="badge rounded-pill text-bg-success align-middle ms-2">
                    <i class="fas fa-check me-1"></i>Emitida
                </span>
            </h2>
            <p class="text-muted">
                Factura {{ $numeroFactura }} generada correctamente.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('factura.imprimir', ['factura' => $factura->factura_id]) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-print me-1"></i> Imprimir Factura
            </a>
            <a href="{{ route('ventas') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Ventas
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 overflow-hidden">

        <div class="bg-primary" style="height:6px;"></div>

        <div class="card-body p-4 p-md-5">

            <div class="row g-4 align-items-start mb-4">

                <div class="col-md-7 text-center text-md-start">
                    @if (!empty($configuracion->nombre_negocio))
                        <h4 class="fw-bold text-uppercase text-primary mb-1">{{ $configuracion->nombre_negocio }}</h4>
                        <div class="small text-muted">{{ $configuracion->actividad ?? '' }}</div>
                        <div class="small text-muted">{{ $configuracion->direccion ?? '' }}</div>
                        <div class="small text-muted">
                            Tel.: {{ $configuracion->telefono ?? '' }} &mdash; {{ $configuracion->ciudad ?? '' }}
                        </div>
                        <div class="small text-muted">RUC: <strong>{{ $configuracion->ruc ?? '' }}</strong></div>
                    @else
                        <h4 class="fw-bold text-primary mb-0">Factura</h4>
                    @endif
                </div>

                <div class="col-md-5">
                    <div class="border rounded-3 p-3 text-center bg-light">
                        <div class="text-muted small text-uppercase fw-semibold">Factura N.º</div>
                        <div class="font-monospace fw-bold text-primary fs-4" style="letter-spacing:1px;">{{ $numeroFactura }}</div>
                        <div class="d-flex justify-content-center gap-3 small mt-2">
                            <span><span class="text-muted">Timbrado:</span> <strong>{{ $factura->timbrado ?? $configuracion->timbrado ?? '' }}</strong></span>
                            <span><span class="text-muted">Fecha:</span> <strong>{{ $fechaEmision ?: '-' }}</strong></span>
                        </div>
                    </div>
                </div>

            </div>

            <hr class="my-4">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1"><i class="fas fa-user me-1"></i>Cliente / Razón social</span>
                    <strong class="fs-6">{{ $factura->cliente_nombre ?? '' }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1"><i class="fas fa-id-card me-1"></i>RUC / C.I.</span>
                    <strong class="fs-6">{{ $factura->cliente_documento ?? '' }}</strong>
                </div>
            </div>

            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">Cant.</th>
                            <th>Descripción</th>
                            <th class="text-end">Precio unit.</th>
                            <th class="text-end">Exenta</th>
                            <th class="text-end">IVA 5%</th>
                            <th class="text-end">IVA 10%</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($detalle->isEmpty())
                            <tr><td colspan="7" class="text-center text-muted py-4">Sin detalles.</td></tr>
                        @else
                            @foreach ($detalle as $item)
                                <tr>
                                    <td class="text-center">{{ number_format($item->cantidad ?? 0, 2, ',', '.') }}</td>
                                    <td>{{ $item->descripcion ?? '' }}</td>
                                    <td class="text-end">Gs. {{ number_format($item->precio_unitario ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($item->exenta ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($item->gravada_5 ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($item->gravada_10 ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Gs. {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end g-4 mt-1">
                <div class="col-md-6 col-lg-5">
                    <div class="rounded-3 border">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted ps-3">Total exenta</td>
                                    <td class="text-end pe-3">Gs. {{ number_format($factura->total_exenta ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">IVA 5%</td>
                                    <td class="text-end pe-3">Gs. {{ number_format($factura->total_iva_5 ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">IVA 10%</td>
                                    <td class="text-end pe-3">Gs. {{ number_format($factura->total_iva_10 ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">Total IVA</td>
                                    <td class="text-end pe-3">Gs. {{ number_format($factura->total_iva ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">Artículos / Unidades</td>
                                    <td class="text-end pe-3">{{ count($detalle) }} / {{ number_format($totalCantidad, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="bg-success text-white d-flex justify-content-between align-items-center px-3 py-3 rounded-bottom">
                            <span class="fw-bold text-uppercase">Total a pagar</span>
                            <span class="fs-4 fw-bold">Gs. {{ number_format($factura->total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
