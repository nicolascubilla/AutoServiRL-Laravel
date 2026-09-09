@extends('layouts.app')

@section('title', 'Historial de Facturas | Autoservice R & L')

@section('content')
@php
$totalFacturas = $facturas->total();
@endphp
<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-invoice me-2 text-primary"></i>Historial de Facturas
            </h2>
            <p class="text-muted mb-0">
                Consulte las facturas emitidas, visualícelas o vuelva a imprimirlas.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('facturacion.config') }}" class="btn btn-outline-primary">
                <i class="fas fa-gear me-1"></i> Configuración
            </a>
            <a href="{{ route('ventas') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nueva Factura
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
<input type="text" id="buscarFactura" class="form-control" placeholder="Buscar por número, cliente o documento..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15" {{ request('porPagina') == 15 ? 'selected' : '' }}>15 por página</option>
                        <option value="30" {{ request('porPagina') == 30 || request('porPagina') === null ? 'selected' : '' }}>30 por página</option>
                        <option value="50" {{ request('porPagina') == 50 ? 'selected' : '' }}>50 por página</option>
                        <option value="100" {{ request('porPagina') == 100 ? 'selected' : '' }}>100 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-4 text-md-end">
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary" id="resumenTotal">
                        <i class="fas fa-file-invoice me-1"></i> <span id="cantTotal">{{ number_format($totalFacturas, 0, ',', '.') }}</span> factura(s)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaFacturas">
                    <thead class="table-light">
                        <tr>
                            <th>Nº Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $f)
                            @php
                            $estado = ($f->estado ?? 'A');
                            $numPadded = str_pad((string)($f->numero_factura ?? ''), 7, '0', STR_PAD_LEFT);
                            $numCompleto = ($f->establecimiento ?? '') . '-' . ($f->punto_expedicion ?? '') . '-' . $numPadded;
                            @endphp
                            <tr data-numero="{{ mb_strtolower($numCompleto) }}"
                                data-numero-sec="{{ mb_strtolower((string)($f->numero_factura ?? '')) }}"
                                data-cliente="{{ mb_strtolower($f->cliente_nombre ?? '') }}"
                                data-documento="{{ mb_strtolower($f->cliente_documento ?? '') }}">
                                <td>
                                    <span class="text-muted small fw-semibold">{{ $numCompleto }}</span>
                                    <small class="d-block text-muted">Venta #{{ (int)$f->venta_id }}</small>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($f->fecha_emision)) }}</td>
                                <td>
                                    <strong>{{ $f->cliente_nombre }}</strong>
                                    <small class="d-block text-muted">Doc: {{ $f->cliente_documento }}</small>
                                </td>
                                <td class="text-end fw-semibold">Gs. {{ number_format($f->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if ($estado !== 'A')
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary">Anulada</span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">Activa</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-outline-primary" title="Ver factura"
                                            href="{{ route('factura.ver', ['factura' => $f->factura_id]) }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-outline-success" title="Imprimir factura"
                                            href="{{ route('factura.imprimir', ['factura' => $f->factura_id]) }}"
                                            target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

@if ($totalFacturas === 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-file-invoice fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron facturas.
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <small class="text-muted">
                        Mostrando {{ $facturas->firstItem() }}–{{ $facturas->lastItem() }}
                        de {{ number_format($totalFacturas, 0, ',', '.') }} factura(s)
                    </small>
                    {{ $facturas->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscarFactura = document.getElementById('buscarFactura');
    const porPagina = document.getElementById('porPagina');

    function irHistorial() {
        const url = new URL(window.location.href);
        const q = buscarFactura.value.trim();
        if (q) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.set('porPagina', porPagina.value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    let timer = null;
    buscarFactura.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(irHistorial, 400);
    });

    porPagina.addEventListener('change', irHistorial);
});
</script>
@endsection
