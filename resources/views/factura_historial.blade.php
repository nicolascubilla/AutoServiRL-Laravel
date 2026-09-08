@extends('layouts.app')

@section('title', 'Historial de Facturas | Autoservice R & L')

@section('content')
@php
$totalFacturas = $facturas->count();
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
                        <input type="text" id="buscarFactura" class="form-control" placeholder="Buscar por número o cliente...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15">15 por página</option>
                        <option value="30" selected>30 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-4 text-md-end">
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary" id="resumenTotal">
                        <i class="fas fa-file-invoice me-1"></i> <span id="cantTotal">{{ $totalFacturas }}</span> factura(s)
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
                    Aún no se han emitido facturas.
                </div>
            @endif

            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted" id="infoFactura"></small>
                <ul class="pagination pagination-sm mb-0" id="paginadorFactura"></ul>
            </nav>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const $filas = Array.prototype.slice.call(document.querySelectorAll('#tablaFacturas tbody tr'));
    const paginador = document.getElementById('paginadorFactura');
    const info = document.getElementById('infoFactura');

    if ($filas.length === 0) return;

    let porPagina = parseInt(document.getElementById('porPagina').value, 10);
    let paginaActual = 1;

    function filaCoincide(tr, q) {
        return !q ||
            (tr.dataset.numero || '').includes(q) ||
            (tr.dataset.numeroSec || '').includes(q) ||
            (tr.dataset.documento || '').includes(q) ||
            (tr.dataset.cliente || '').includes(q);
    }

    function getVisibles(q) {
        return $filas.filter(tr => filaCoincide(tr, q));
    }

    document.getElementById('cantTotal').textContent = document.querySelectorAll('#tablaFacturas tbody tr').length;

    function renderPagina() {
        const q = document.getElementById('buscarFactura').value.trim().toLowerCase();
        const visibles = getVisibles(q);
        const totalPaginas = Math.max(1, Math.ceil(visibles.length / porPagina));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const desde = (paginaActual - 1) * porPagina;
        const hasta = Math.min(desde + porPagina, visibles.length);

        $filas.forEach(tr => tr.style.display = 'none');
        for (let i = desde; i < hasta; i++) {
            visibles[i].style.display = '';
        }

        info.textContent = visibles.length > 0
            ? 'Mostrando ' + (desde + 1) + '\u2013' + hasta + ' de ' + visibles.length + ' factura(s)'
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

    document.getElementById('buscarFactura').addEventListener('keyup', function () {
        paginaActual = 1;
        renderPagina();
    });

    renderPagina();
});
</script>
@endsection
