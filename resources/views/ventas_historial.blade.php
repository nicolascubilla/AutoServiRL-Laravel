@extends('layouts.app')

@section('title', 'Historial de Ventas | Autoservice R &amp; L')

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
                        <input type="text" id="buscarVenta" class="form-control" placeholder="Buscar por nº venta o cajero...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="filtroEstado" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="1">Facturadas</option>
                        <option value="0">Sin facturar</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15">15 por página</option>
                        <option value="30" selected>30 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-2 text-md-end">
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary" id="resumenTotal">
                        <i class="fas fa-cash-register me-1"></i> <span id="cantTotal">{{ $historialVentas->count() }}</span>
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

            @if ($historialVentas->count() === 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-cash-register fs-1 d-block mb-2 opacity-50"></i>
                    Aún no se han registrado ventas.
                </div>
            @endif

            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted" id="infoVenta"></small>
                <ul class="pagination pagination-sm mb-0" id="paginadorVenta"></ul>
            </nav>
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

    const $filas = Array.prototype.slice.call(document.querySelectorAll('#tablaVentas tbody tr'));
    const paginador = document.getElementById('paginadorVenta');
    const info = document.getElementById('infoVenta');

    if ($filas.length === 0) return;

    let porPagina = parseInt(document.getElementById('porPagina').value, 10);
    let paginaActual = 1;

    function filaCoincide(tr, q, estado) {
        const okEstado = estado === '' || tr.dataset.facturada === estado;
        if (!okEstado) return false;
        return !q ||
            (tr.dataset.numero || '').includes(q) ||
            (tr.dataset.cajero || '').includes(q);
    }

    function getVisibles(q, estado) {
        return $filas.filter(tr => filaCoincide(tr, q, estado));
    }

    document.getElementById('cantTotal').textContent = document.querySelectorAll('#tablaVentas tbody tr').length;

    function renderPagina() {
        const q = document.getElementById('buscarVenta').value.trim().toLowerCase();
        const estado = document.getElementById('filtroEstado').value;
        const visibles = getVisibles(q, estado);
        const totalPaginas = Math.max(1, Math.ceil(visibles.length / porPagina));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const desde = (paginaActual - 1) * porPagina;
        const hasta = Math.min(desde + porPagina, visibles.length);

        $filas.forEach(tr => tr.style.display = 'none');
        for (let i = desde; i < hasta; i++) {
            visibles[i].style.display = '';
        }

        info.textContent = visibles.length > 0
            ? 'Mostrando ' + (desde + 1) + '–' + hasta + ' de ' + visibles.length + ' venta(s)'
            : 'Sin resultados';

        paginador.innerHTML = '';
        if (totalPaginas > 1) {
            const btnPrev = document.createElement('li');
            btnPrev.className = 'page-item' + (paginaActual === 1 ? ' disabled' : '');
            btnPrev.innerHTML = '<button class="page-link" data-pagina="' + (paginaActual - 1) + '">&laquo;</button>';
            paginador.appendChild(btnPrev);

            for (let i = 1; i <= totalPaginas; i++) {
                const li = document.createElement('li');
                li.className = 'page-item' + (i === paginaActual ? ' active' : '');
                li.innerHTML = '<button class="page-link" data-pagina="' + i + '">' + i + '</button>';
                paginador.appendChild(li);
            }

            const btnNext = document.createElement('li');
            btnNext.className = 'page-item' + (paginaActual === totalPaginas ? ' disabled' : '');
            btnNext.innerHTML = '<button class="page-link" data-pagina="' + (paginaActual + 1) + '">&raquo;</button>';
            paginador.appendChild(btnNext);
        }
    }

    paginador.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-pagina]');
        if (!btn || btn.getAttribute('data-pagina') < 1) return;
        paginaActual = parseInt(btn.getAttribute('data-pagina'), 10);
        renderPagina();
    });

    document.getElementById('porPagina').addEventListener('change', function () {
        porPagina = parseInt(this.value, 10);
        paginaActual = 1;
        renderPagina();
    });

    document.getElementById('buscarVenta').addEventListener('keyup', function () {
        paginaActual = 1;
        renderPagina();
    });

    document.getElementById('filtroEstado').addEventListener('change', function () {
        paginaActual = 1;
        renderPagina();
    });

    renderPagina();
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
