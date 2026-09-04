@extends('layouts.app')

@section('title', 'Informes | AutoServiRL')

@section('content')
<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-chart-bar me-2 text-primary"></i>Informes
            </h2>
            <p class="text-muted mb-0">
                Reportes de ventas, facturación y productos más vendidos por período.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('informes') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('informes') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 pt-3 mt-1 border-top">
                <a class="btn btn-outline-danger btn-sm"
                    href="{{ route('informes.imprimir', ['desde' => $desde, 'hasta' => $hasta]) }}"
                    target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Imprimir / PDF
                </a>
                <a class="btn btn-outline-success btn-sm"
                    href="{{ route('informes.exportar', ['tipo' => 'ventas', 'desde' => $desde, 'hasta' => $hasta]) }}">
                    <i class="fas fa-file-excel me-1"></i> Exportar Ventas (Excel)
                </a>
                <a class="btn btn-outline-success btn-sm"
                    href="{{ route('informes.exportar', ['tipo' => 'productos', 'desde' => $desde, 'hasta' => $hasta]) }}">
                    <i class="fas fa-file-excel me-1"></i> Exportar Top Productos (Excel)
                </a>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="tabInformes" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-ventas" data-bs-toggle="tab" data-bs-target="#pane-ventas" type="button" role="tab">
                <i class="fas fa-cash-register me-1"></i>Ventas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-facturacion" data-bs-toggle="tab" data-bs-target="#pane-facturacion" type="button" role="tab">
                <i class="fas fa-file-invoice me-1"></i>Facturación
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-productos" data-bs-toggle="tab" data-bs-target="#pane-productos" type="button" role="tab">
                <i class="fas fa-box me-1"></i>Top Productos
            </button>
        </li>
    </ul>

    <div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm pb-3">

        <div class="tab-pane fade show active p-3" id="pane-ventas" role="tabpanel">
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Ventas del período</div>
                        <div class="fs-3 fw-bold text-primary">{{ $informeVentas->cantidad }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Total vendido</div>
                        <div class="fs-4 fw-bold text-success">Gs. {{ number_format($informeVentas->total, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Efectivo</div>
                        <div class="fs-5 fw-bold">Gs. {{ number_format($informeVentas->efectivo, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Transferencia</div>
                        <div class="fs-5 fw-bold">Gs. {{ number_format($informeVentas->transferencia, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <h6 class="fw-semibold text-muted mb-3">Ventas por día</h6>
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th class="text-center">Ventas</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($ventasPorDia->isEmpty())
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas en el período.</td></tr>
                        @else
                            @foreach ($ventasPorDia as $d)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($d->fecha)) }}</td>
                                    <td class="text-center">{{ (int)$d->cantidad }}</td>
                                    <td class="text-end fw-semibold">Gs. {{ number_format($d->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade p-3" id="pane-facturacion" role="tabpanel">
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Facturas emitidas</div>
                        <div class="fs-3 fw-bold text-primary">{{ $informeFacturacion->cantidad }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Total facturado</div>
                        <div class="fs-4 fw-bold text-success">Gs. {{ number_format($informeFacturacion->total, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">IVA total</div>
                        <div class="fs-5 fw-bold">Gs. {{ number_format($informeFacturacion->total_iva, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="rounded-3 border p-3 text-center h-100 bg-light">
                        <div class="text-muted small fw-semibold">Base exenta</div>
                        <div class="fs-5 fw-bold">Gs. {{ number_format($informeFacturacion->total_exenta, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                <i class="fas fa-circle-info"></i>
                <div>
                    En este período se emitieron <strong>{{ $informeFacturacion->cantidad }}</strong> factura(s) por
                    un total de <strong>Gs. {{ number_format($informeFacturacion->total, 0, ',', '.') }}</strong>.
                    Consulte el detalle en el <a href="{{ route('factura.historial') }}" class="alert-link">Historial de Facturas</a>.
                </div>
            </div>
        </div>

        <div class="tab-pane fade p-3" id="pane-productos" role="tabpanel">
            <h6 class="fw-semibold text-muted mb-3">Productos más vendidos del período</h6>
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($topProductos->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin ventas en el período.</td></tr>
                        @else
                            @foreach ($topProductos as $i => $p)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $p->descripcion }}</td>
                                    <td class="text-center">
                                        {{ rtrim(rtrim(number_format($p->cantidad, 3, ',', '.'), '0'), ',') }}
                                    </td>
                                    <td class="text-end fw-semibold">Gs. {{ number_format($p->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
