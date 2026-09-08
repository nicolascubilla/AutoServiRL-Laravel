@extends('layouts.app')

@section('title', 'Stock | Autoservice R &amp; L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <!-- ENCABEZADO -->
    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-warehouse me-2"></i>Stock</h2>
            <p class="text-muted mb-0">Control de existencias y movimientos de productos</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEntrada">
                <i class="fas fa-arrow-down me-1"></i> Registrar Entrada
            </button>
            <button class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalSalida">
                <i class="fas fa-arrow-up me-1"></i> Registrar Salida
            </button>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="buscarStock" class="form-control" placeholder="Buscar por código o descripción...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15">15 por página</option>
                        <option value="30">30 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100" selected>100 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 text-md-end">
                    <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning" id="resumenBajo">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span id="cantBajo">0</span> con stock bajo
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaStock">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Stock mínimo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $p)
                            @php
                            $cantidad = (float) $p->cantidad;
                            $stockMin = (float) $p->stock_minimo;
                            $bajo = $p->activo === 'S' && $cantidad <= $stockMin;
                            @endphp
                            <tr data-descripcion="{{ mb_strtolower($p->descripcion) }}"
                                data-codigo="{{ mb_strtolower($p->codigo ?? '') }}"
                                data-codigo-barra="{{ mb_strtolower($p->codigo_barra ?? '') }}">
                                <td>
                                    <strong>{{ $p->codigo ?? '-' }}</strong>
                                </td>
                                <td>
                                    {{ $p->descripcion }}
                                    <small class="d-block text-muted">Barras: {{ $p->codigo_barra ?? '-' }}</small>
                                </td>
                                <td class="text-end">Gs. {{ number_format($p->precio, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <strong class="{{ $bajo ? 'text-danger' : '' }}">
                                        {{ rtrim(rtrim(number_format($cantidad, 3, ',', '.'), '0'), ',') }}
                                    </strong>
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($stockMin, 3, ',', '.'), '0'), ',') }}</td>
                                <td class="text-center">
                                    @if ($p->activo !== 'S')
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary">Inactivo</span>
                                    @elseif ($bajo)
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">Stock bajo</span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">Disponible</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success btnEntrada" title="Registrar entrada"
                                            data-bs-toggle="modal" data-bs-target="#modalEntrada"
                                            data-pro-cod="{{ $p->pro_cod }}"
                                            data-descripcion="{{ $p->descripcion }}">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btnSalida" title="Registrar salida"
                                            data-bs-toggle="modal" data-bs-target="#modalSalida"
                                            data-pro-cod="{{ $p->pro_cod }}"
                                            data-descripcion="{{ $p->descripcion }}"
                                            data-cantidad="{{ $cantidad }}">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btnMinimo" title="Editar stock mínimo"
                                            data-bs-toggle="modal" data-bs-target="#modalMinimo"
                                            data-pro-cod="{{ $p->pro_cod }}"
                                            data-descripcion="{{ $p->descripcion }}"
                                            data-minimo="{{ $stockMin }}">
                                            <i class="fas fa-sliders-h"></i>
                                        </button>
                                        <button class="btn btn-outline-info btnHistorial" title="Ver historial de movimientos"
                                            data-bs-toggle="modal" data-bs-target="#modalHistorial"
                                            data-pro-cod="{{ $p->pro_cod }}"
                                            data-descripcion="{{ $p->descripcion }}"
                                            data-codigo="{{ $p->codigo ?? '' }}">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted" id="infoStock"></small>
                <ul class="pagination pagination-sm mb-0" id="paginadorStock"></ul>
            </nav>
        </div>
    </div>

</div>

<!-- ==========================================
     MODAL ENTRADA
========================================== -->
<div class="modal fade" id="modalEntrada" tabindex="-1" aria-labelledby="modalEntradaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('stock.entrada') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEntradaLabel">
                    <i class="fas fa-arrow-down me-2 text-success"></i>Registrar Entrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select name="pro_cod" class="form-select" required>
                        <option value="">Seleccione un producto...</option>
                        @foreach ($productos as $p)
                            <option value="{{ $p->pro_cod }}">{{ $p->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="text" name="cantidad" class="form-control auto-cantidad" autocomplete="off" required>
                </div>
                <div class="mb-1">
                    <label class="form-label">Observación (opcional)</label>
                    <input type="text" name="observacion" class="form-control" maxlength="255" placeholder="Ej. Reposición, compra a proveedor...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-arrow-down me-1"></i> Registrar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL SALIDA
========================================== -->
<div class="modal fade" id="modalSalida" tabindex="-1" aria-labelledby="modalSalidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('stock.salida') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalSalidaLabel">
                    <i class="fas fa-arrow-up me-2 text-warning"></i>Registrar Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select name="pro_cod" class="form-select" required>
                        <option value="">Seleccione un producto...</option>
                        @foreach ($productos as $p)
                            <option value="{{ $p->pro_cod }}">{{ $p->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="text" name="cantidad" class="form-control auto-cantidad" autocomplete="off" required>
                </div>
                <div class="alert alert-light border small mb-3 d-none" id="avisoSalida">
                    <i class="fas fa-info-circle me-1"></i>
                    Stock actual del producto seleccionado: <strong id="salidaStockActual">0</strong>
                </div>
                <div class="mb-1">
                    <label class="form-label">Observación (opcional)</label>
                    <input type="text" name="observacion" class="form-control" maxlength="255" placeholder="Ej. Merma, desperfecto, uso interno...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning text-dark">
                    <i class="fas fa-arrow-up me-1"></i> Registrar Salida
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL STOCK MÍNIMO
========================================== -->
<div class="modal fade" id="modalMinimo" tabindex="-1" aria-labelledby="modalMinimoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('stock.minimo') }}" class="modal-content">
            @csrf
            <input type="hidden" name="pro_cod" id="minimo_pro_cod">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMinimoLabel">
                    <i class="fas fa-sliders-h me-2 text-primary"></i>Editar Stock Mínimo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="minimo_descripcion"></p>
                <div class="mb-1">
                    <label class="form-label">Stock mínimo <span class="text-danger">*</span></label>
                    <input type="text" name="stock_minimo" id="minimo_valor" class="form-control auto-cantidad" autocomplete="off" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL HISTORIAL DE MOVIMIENTOS
========================================== -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialLabel">
                    <i class="fas fa-history me-2 text-info"></i>Historial de Movimientos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="historialInfo" class="mb-3"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Stock result.</th>
                                <th>Observación</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody id="historialBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

<script>
    /* ======================================================
       ESTADO DE PAGINACIÓN
    ====================================================== */
    const $filasStock = Array.prototype.slice.call(
        document.querySelectorAll('#tablaStock tbody tr')
    );
    let paginaActual = 1;
    let porPagina = parseInt(document.getElementById('porPagina').value, 10);

    const paginador = document.getElementById('paginadorStock');
    const infoStock = document.getElementById('infoStock');

    /* ======================================================
       CONTADOR DE STOCK BAJO (sobre TODAS las filas)
    ====================================================== */
    function contarStockBajo() {
        let contador = 0;
        $filasStock.forEach(tr => {
            const badge = tr.querySelector('.badge');
            if (badge && badge.textContent.trim() === 'Stock bajo') contador++;
        });
        document.getElementById('cantBajo').textContent = contador;
    }

    /* ======================================================
       BÚSQUEDA LOCAL
    ====================================================== */
    function filaCoincide(tr, q) {
        return !q ||
            (tr.dataset.descripcion || '').includes(q) ||
            (tr.dataset.codigo || '').includes(q) ||
            (tr.dataset.codigoBarra || '').includes(q);
    }

    function getVisibles(q) {
        return $filasStock.filter(tr => filaCoincide(tr, q));
    }

    /* ======================================================
       RENDER DE PÁGINA
    ====================================================== */
    function renderPagina() {
        const q = document.getElementById('buscarStock').value.trim().toLowerCase();
        const visibles = getVisibles(q);
        const totalPaginas = Math.max(1, Math.ceil(visibles.length / porPagina));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const desde = (paginaActual - 1) * porPagina;
        const hasta = Math.min(desde + porPagina, visibles.length);

        // Ocultar todas
        $filasStock.forEach(tr => tr.style.display = 'none');
        // Mostrar las de la página actual (dentro del filtrado)
        for (let i = desde; i < hasta; i++) {
            visibles[i].style.display = '';
        }

        // Info
        infoStock.textContent = visibles.length > 0
            ? 'Mostrando ' + (desde + 1) + '–' + hasta + ' de ' + visibles.length + ' producto(s)'
            : 'Sin resultados';

        // Paginador
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

    paginador.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-pagina]');
        if (!btn || btn.getAttribute('data-pagina') < 1) return;
        paginaActual = parseInt(btn.getAttribute('data-pagina'), 10);
        renderPagina();
    });

    document.getElementById('porPagina').addEventListener('change', function() {
        porPagina = parseInt(this.value, 10);
        paginaActual = 1;
        renderPagina();
    });

    document.getElementById('buscarStock').addEventListener('keyup', function() {
        paginaActual = 1;
        renderPagina();
    });

    /* ======================================================
       SELECT2 EN LOS SELECTS DE PRODUCTO
    ====================================================== */
    const selEntrada = document.querySelector('#modalEntrada select[name="pro_cod"]');
    const selSalida = document.querySelector('#modalSalida select[name="pro_cod"]');

    if (window.jQuery && selEntrada) {
        jQuery(selEntrada).select2({
            dropdownParent: jQuery('#modalEntrada'),
            placeholder: 'Busque y seleccione un producto...',
            allowClear: true,
            width: '100%',
            language: 'es'
        });
    }
    if (window.jQuery && selSalida) {
        jQuery(selSalida).select2({
            dropdownParent: jQuery('#modalSalida'),
            placeholder: 'Busque y seleccione un producto...',
            allowClear: true,
            width: '100%',
            language: 'es'
        });
    }

    /* ======================================================
       AUTONUMERIC PARA CANTIDADES
    ====================================================== */
    document.querySelectorAll('.auto-cantidad').forEach(el => {
        new AutoNumeric(el, {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 3,
            minimumValue: '0'
        });
    });

    /* Convertir a numérico antes de enviar */
    document.querySelectorAll('form.modal-content').forEach(f => {
        f.addEventListener('submit', function() {
            this.querySelectorAll('.auto-cantidad').forEach(el => {
                const an = AutoNumeric.getAutoNumericElement(el);
                if (an) el.value = an.getNumericString();
            });
        });
    });

    /* ======================================================
       MODAL SALIDA: mostrar stock actual del producto
    ====================================================== */
    const avisoSalida = document.getElementById('avisoSalida');
    const salidaStockActual = document.getElementById('salidaStockActual');

    function mostrarStockActual(pro_cod) {
        if (!pro_cod) {
            avisoSalida.classList.add('d-none');
            return;
        }
        fetch('{{ route('stock.historial') }}?pro_cod=' + pro_cod)
            .then(r => r.json())
            .then(data => {
                const cantidad = data.success
                    ? parseFloat(data.reporte.cantidad || 0)
                    : 0;
                salidaStockActual.textContent = cantidad.toLocaleString('es-PY', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3
                });
                avisoSalida.classList.remove('d-none');
            });
    }

    if (selSalida) {
        jQuery(selSalida).on('change', function() {
            mostrarStockActual(this.value);
        });
    }

    /* ======================================================
       BOTONES DENTRO DE LA TABLA
    ====================================================== */
    document.querySelectorAll('.btnEntrada').forEach(btn => {
        btn.addEventListener('click', function() {
            if (window.jQuery) jQuery(selEntrada).val(this.dataset.proCod).trigger('change');
            else selEntrada.value = this.dataset.proCod;
        });
    });

    document.querySelectorAll('.btnSalida').forEach(btn => {
        btn.addEventListener('click', function() {
            if (window.jQuery) jQuery(selSalida).val(this.dataset.proCod).trigger('change');
            else selSalida.value = this.dataset.proCod;
            mostrarStockActual(this.dataset.proCod);
        });
    });

    document.querySelectorAll('.btnMinimo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('minimo_pro_cod').value = this.dataset.proCod;
            document.getElementById('minimo_descripcion').textContent = this.dataset.descripcion;
            AutoNumeric.getAutoNumericElement('#minimo_valor').set(this.dataset.minimo);
        });
    });

    /* ======================================================
       MODAL HISTORIAL DE MOVIMIENTOS
    ====================================================== */
    const modalHistorialEl = document.getElementById('modalHistorial');
    const historialBody = document.getElementById('historialBody');
    const historialInfo = document.getElementById('historialInfo');

    function formatearNum(n) {
        return parseFloat(n || 0).toLocaleString('es-PY', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3
        });
    }

    function formatearFecha(f) {
        if (!f) return '-';
        const d = new Date(f.replace(' ', 'T'));
        if (isNaN(d)) return f;
        return d.toLocaleString('es-PY', { dateStyle: 'short', timeStyle: 'short' });
    }

    document.querySelectorAll('.btnHistorial').forEach(btn => {
        btn.addEventListener('click', async function() {
            const pro_cod = this.dataset.proCod;
            const desc = this.dataset.descripcion;
            historialInfo.innerHTML =
                '<span class="fw-semibold">' + this.dataset.codigo + '</span> &mdash; <span class="text-muted">' + desc + '</span>';
            historialBody.innerHTML =
                '<tr><td colspan="6" class="text-center text-muted py-3">Cargando... <i class="fas fa-spinner fa-spin ms-1"></i></td></tr>';

            if (window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalHistorialEl).show();
            }

            try {
                const resp = await fetch('{{ route('stock.historial') }}?pro_cod=' + pro_cod);
                const data = await resp.json();
                const movs = data.movimientos || [];

                if (movs.length === 0) {
                    historialBody.innerHTML =
                        '<tr><td colspan="6" class="text-center text-muted py-3">Sin movimientos registrados.</td></tr>';
                } else {
                    historialBody.innerHTML = '';
                    movs.forEach(m => {
                        const tr = document.createElement('tr');

                        const entrada = m.tipo === 'entrada';
                        const esVenta = (m.observacion || '').trim() === 'Venta';

                        let badge, claseSigno, signo;
                        if (entrada) {
                            badge = '<span class="badge rounded-pill bg-success-subtle text-success border border-success"><i class="fas fa-arrow-down me-1"></i>Entrada</span>';
                            claseSigno = ' text-success';
                            signo = '+';
                        } else if (esVenta) {
                            badge = '<span class="badge rounded-pill bg-danger-subtle text-danger border border-danger"><i class="fas fa-cart-shopping me-1"></i>Venta</span>';
                            claseSigno = ' text-danger';
                            signo = '-';
                        } else {
                            badge = '<span class="badge rounded-pill bg-warning-subtle text-warning border border-warning"><i class="fas fa-arrow-up me-1"></i>Salida manual</span>';
                            claseSigno = ' text-danger';
                            signo = '-';
                        }

                        tr.innerHTML =
                            '<td class="text-nowrap small">' + formatearFecha(m.fecha_movimiento) + '</td>' +
                            '<td class="text-center">' + badge + '</td>' +
                            '<td class="text-end' + claseSigno + ' fw-semibold">' +
                                signo + ' ' + formatearNum(m.cantidad) + '</td>' +
                            '<td class="text-end">' + formatearNum(m.stock_resultante) + '</td>' +
                            '<td class="small">' + (m.observacion || '-') + '</td>' +
                            '<td class="small">' + (m.usuario || 'Sistema') + '</td>';
                        historialBody.appendChild(tr);
                    });
                }
            } catch (e) {
                historialBody.innerHTML =
                    '<tr><td colspan="6" class="text-center text-danger py-3">Error al cargar el historial.</td></tr>';
            }
        });
    });

    /* ======================================================
       INICIALIZACIÓN
    ====================================================== */
    renderPagina();
    contarStockBajo();

    /* ======================================================
       ALERTAS AUTO-CIERRE (éxito 3s, error 5s)
    ====================================================== */
    document.addEventListener("DOMContentLoaded", function() {
        var exito = document.getElementById('mensajeSuccess');
        if (exito) setTimeout(function() {
            bootstrap.Alert.getOrCreateInstance(exito).close();
        }, 3000);

        var error = document.getElementById('mensajeError');
        if (error) setTimeout(function() {
            bootstrap.Alert.getOrCreateInstance(error).close();
        }, 5000);
    });
</script>
@endsection