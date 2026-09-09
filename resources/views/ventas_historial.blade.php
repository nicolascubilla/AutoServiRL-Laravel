@extends('layouts.app')

@section('title', 'Historial de Ventas | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-cash-register me-2 text-primary"></i>Historial de Ventas
            </h2>
            <p class="text-muted mb-0">
                Consulte las ventas realizadas, su estado de facturación, el ticket y las facturas.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ventas') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nueva Venta
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
                        <input type="text" id="buscarVenta" class="form-control" placeholder="Buscar por nº venta o cajero..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="filtroEstado" class="form-select form-select-sm">
                        <option value="" {{ request('estado') === '' || request('estado') === null ? 'selected' : '' }}>Todas</option>
                        <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Facturadas</option>
                        <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Sin facturar</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15" {{ request('porPagina') == 15 ? 'selected' : '' }}>15 por página</option>
                        <option value="30" {{ request('porPagina') == 30 || request('porPagina') === null ? 'selected' : '' }}>30 por página</option>
                        <option value="50" {{ request('porPagina') == 50 ? 'selected' : '' }}>50 por página</option>
                        <option value="100" {{ request('porPagina') == 100 ? 'selected' : '' }}>100 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-2 text-md-end">
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary" id="resumenTotal">
                        <i class="fas fa-cash-register me-1"></i> <span id="cantTotal">{{ number_format($historialVentas->total(), 0, ',', '.') }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaVentas">
                    <thead class="table-light">
                        <tr>
                            <th>Nº Venta</th>
                            <th>Fecha</th>
                            <th>Cajero</th>
                            <th>Pago</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Facturación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historialVentas as $v)
                            @php
                            $facturada = !empty($v->factura_id);
                            $anulada = ($v->estado ?? '') === 'A';
                            $numFactura = '';
                            if ($facturada) {
                                $numPadded = str_pad((string)($v->numero_factura ?? ''), 7, '0', STR_PAD_LEFT);
                                $numFactura = ($v->establecimiento ?? '') . '-' . ($v->punto_expedicion ?? '') . '-' . $numPadded;
                            }
                            $forma = $v->forma_pago === 'T' ? 'Transferencia' : 'Efectivo';
                            @endphp
                            <tr data-numero="{{ strtolower((string)$v->venta_id) }}"
                                data-cajero="{{ strtolower($v->cajero ?? '') }}"
                                data-facturada="{{ $facturada ? '1' : '0' }}">
                                <td>
                                    <strong>#{{ (int)$v->venta_id }}</strong>
                                    <small class="d-block text-muted">Caja #{{ (int)$v->caja_id }}</small>
                                </td>
                                <td>{{ date('d/m/Y H:i', strtotime($v->fecha_venta)) }}</td>
                                <td>{{ $v->cajero ?? '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $v->forma_pago === 'T' ? 'bg-info-subtle text-info border border-info' : 'bg-secondary-subtle text-secondary border border-secondary' }}">
                                        <i class="fas {{ $v->forma_pago === 'T' ? 'fa-building-columns' : 'fa-money-bill-wave' }} me-1"></i>{{ $forma }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">Gs. {{ number_format($v->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if ($anulada)
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">Anulada</span>
                                    @elseif ($facturada)
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">Facturada</span>
                                        <small class="d-block text-muted mt-1">{{ $numFactura }}</small>
                                    @else
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning">Sin facturar</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($anulada)
                                        <span class="badge bg-light text-muted">Sin acciones</span>
                                    @else
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-outline-secondary" title="Ver ticket"
                                            href="{{ route('ticket', ['venta' => (int)$v->venta_id]) }}">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        @if ($facturada)
                                            <a class="btn btn-outline-primary" title="Ver factura"
                                                href="{{ route('factura.ver', ['factura' => (int)$v->factura_id]) }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a class="btn btn-outline-success" title="Imprimir factura"
                                                href="{{ route('factura.imprimir', ['factura' => (int)$v->factura_id]) }}"
                                                target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @else
                                            <a class="btn btn-outline-primary" title="Emitir factura"
                                                href="{{ route('facturacion', ['venta' => (int)$v->venta_id]) }}">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger" title="Anular venta"
                                            data-bs-toggle="modal" data-bs-target="#modalAnularVenta"
                                            data-venta-id="{{ (int)$v->venta_id }}"
                                            data-total="{{ number_format($v->total, 0, ',', '.') }}">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($historialVentas->total() === 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-cash-register fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron ventas.
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <small class="text-muted">
                        Mostrando {{ $historialVentas->firstItem() }}–{{ $historialVentas->lastItem() }}
                        de {{ number_format($historialVentas->total(), 0, ',', '.') }} venta(s)
                    </small>
                    {{ $historialVentas->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<div class="modal fade" id="modalAnularVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('venta.anular') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban text-danger me-2"></i>Anular Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea anular la venta <strong id="anularVentaNum"></strong>?</p>
                <p class="text-muted small mb-3">
                    Se reintegrará el stock de los productos y se descontará el monto de la caja.
                </p>
                <div class="mb-3">
                    <label class="form-label">Monto de la venta</label>
                    <input type="text" class="form-control" id="anularVentaTotal" disabled>
                </div>
                <input type="hidden" name="venta_id" id="anularVentaId">
                <div>
                    <label class="form-label">Motivo (opcional)</label>
                    <input type="text" name="motivo" class="form-control" placeholder="Ej: venta equivocada">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban me-1"></i> Anular Venta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var msgSuccess = document.getElementById('mensajeSuccess');
    var msgError = document.getElementById('mensajeError');
    if (msgSuccess) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(msgSuccess).close();
        }, 3000);
    }
    if (msgError) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(msgError).close();
        }, 5000);
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscarVenta = document.getElementById('buscarVenta');
    const filtroEstado = document.getElementById('filtroEstado');
    const porPagina = document.getElementById('porPagina');

    function irHistorial() {
        const url = new URL(window.location.href);
        const q = buscarVenta.value.trim();
        if (q) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }
        if (filtroEstado.value) {
            url.searchParams.set('estado', filtroEstado.value);
        } else {
            url.searchParams.delete('estado');
        }
        url.searchParams.set('porPagina', porPagina.value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    let timer = null;
    buscarVenta.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(irHistorial, 400);
    });

    filtroEstado.addEventListener('change', irHistorial);
    porPagina.addEventListener('change', irHistorial);
});
</script>
<script>
document.querySelectorAll('[data-bs-target="#modalAnularVenta"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('anularVentaId').value = btn.getAttribute('data-venta-id');
        document.getElementById('anularVentaNum').textContent = '#' + btn.getAttribute('data-venta-id');
        document.getElementById('anularVentaTotal').value = 'Gs. ' + btn.getAttribute('data-total');
    });
});
</script>
@endsection
