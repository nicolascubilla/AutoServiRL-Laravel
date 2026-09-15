@extends('layouts.app')

@section('title', 'Productos | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <!-- ENCABEZADO -->
    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Productos</h2>
            <p class="text-muted mb-0">Gestione el catálogo de productos del negocio</p>
        </div>
        <div class="ms-auto d-flex flex-wrap gap-2">
            <a href="{{ route('productos.importar') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-import me-1"></i> Importar Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="buscarProducto" class="form-control"
                            placeholder="Código, barras o descripción..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <select id="porPagina" class="form-select form-select-sm">
                        <option value="15" {{ request('porPagina') == 15 ? 'selected' : '' }}>15 por página</option>
                        <option value="30" {{ request('porPagina') == 30 ? 'selected' : '' }}>30 por página</option>
                        <option value="50" {{ request('porPagina') == 50 || request('porPagina') === null ? 'selected' : '' }}>50 por página</option>
                        <option value="100" {{ request('porPagina') == 100 ? 'selected' : '' }}>100 por página</option>
                        <option value="200" {{ request('porPagina') == 200 ? 'selected' : '' }}>200 por página</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 text-md-end">
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary">
                        <i class="fas fa-boxes me-1"></i> <span id="cantProductos">{{ number_format($productos->total(), 0, ',', '.') }}</span> producto(s)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaProductos">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cód. Barra</th>
                            <th>Descripción</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">IVA</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $p)
                            @php
                            $activo = $p->activo === 'S';
                            $manejaStock = ($p->maneja_stock ?? 'S') === 'S';
                            @endphp
                            <tr data-pro-cod="{{ (int) $p->pro_cod }}"
                                data-codigo="{{ $p->codigo ?? '' }}"
                                data-codigo-barra="{{ $p->codigo_barra }}"
                                data-descripcion="{{ $p->descripcion }}"
                                data-precio="{{ (int) $p->precio }}"
                                data-tasa-iva="{{ $p->tasa_iva }}"
                                data-maneja-stock="{{ $p->maneja_stock ?? 'S' }}"
                                data-activo="{{ $p->activo }}"
                                data-cantidad="{{ $p->cantidad }}"
                                data-stock-minimo="{{ $p->stock_minimo }}">
                                <td><strong>{{ $p->codigo ?? '-' }}</strong></td>
                                <td>{{ $p->codigo_barra }}</td>
                                <td>
                                    {{ $p->descripcion }}
                                    @if (!$manejaStock)
                                        <small class="d-block text-muted">Sin control de stock</small>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">Gs. {{ number_format($p->precio, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if ($p->tasa_iva === 'E')
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary">Exenta</span>
                                    @else
                                        IVA {{ $p->tasa_iva }}%
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($activo)
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">Activo</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-warning btnEditar" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn {{ $activo ? 'btn-outline-danger' : 'btn-outline-success' }} btnEstado"
                                            title="{{ $activo ? 'Desactivar' : 'Activar' }}">
                                            <i class="fas {{ $activo ? 'fa-power-off' : 'fa-check' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($productos->total() === 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-boxes fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron productos.
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <small class="text-muted">
                        Mostrando {{ $productos->firstItem() }}–{{ $productos->lastItem() }}
                        de {{ number_format($productos->total(), 0, ',', '.') }} producto(s)
                    </small>
                    {{ $productos->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- ===========================
MODAL PRODUCTO
=========================== -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="frmProducto" method="POST" action="{{ route('productos.guardar') }}" class="modal-content">
            @csrf
            <input type="hidden" id="pro_cod" name="pro_cod">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductoLabel">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Código Interno</label>
                    <input type="text" name="codigo" class="form-control" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Código de Barras <span class="text-danger">*</span></label>
                    <input type="text" name="codigo_barra" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control" required autocomplete="off" spellcheck="false">
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio <span class="text-danger">*</span></label>
                    <input type="text" id="precio" name="precio" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tasa IVA <span class="text-danger">*</span></label>
                    <select name="tasa_iva" id="tasa_iva" class="form-select" required>
                        <option value="10">IVA 10%</option>
                        <option value="5">IVA 5%</option>
                        <option value="E">Exenta</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-center">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="maneja_stock" name="maneja_stock" value="S" checked>
                            <label class="form-check-label" for="maneja_stock">¿El producto maneja stock?</label>
                        </div>
                    </div>
                    <small class="text-muted d-block text-center">
                        Desmarque para productos a granel o por peso, que se venden sin control de stock.
                    </small>
                </div>

                <div class="row g-3" id="camposStock">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label">Cantidad inicial</label>
                            <input type="text" id="cantidad" name="cantidad" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label">Stock mínimo</label>
                            <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div id="hintCantidad">
                    <small class="text-muted d-block mt-1">
                        La cantidad inicial solo aplica al crear el producto. Para modificarla luego, use el módulo de Stock.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let modoEdicion = false;
    let timerBusqueda = null;

    /*
     * Búsqueda y paginación del lado del servidor: cada página se consulta a la
     * base de datos, por lo que el catálogo puede crecer (1000+) sin volcar
     * todos los registros a la página al mismo tiempo.
     */
    const buscarProducto = document.getElementById('buscarProducto');
    const porPagina = document.getElementById('porPagina');

    function recargar() {
        const url = new URL(window.location.href);
        const q = buscarProducto.value.trim();
        if (q) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.set('porPagina', porPagina.value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    buscarProducto.addEventListener('input', function () {
        clearTimeout(timerBusqueda);
        timerBusqueda = setTimeout(recargar, 400);
    });

    porPagina.addEventListener('change', recargar);

    /* ======================================================
       ACCIONES DE FILA (delegación de eventos)
    ====================================================== */
    const tabla = document.getElementById('tablaProductos');

    tabla.addEventListener('click', function (e) {
        const btn = e.target.closest('.btnEditar, .btnEstado');
        if (!btn) return;

        const tr = btn.closest('tr');
        const producto = {
            pro_cod: tr.dataset.proCod,
            codigo: tr.dataset.codigo || "",
            codigo_barra: tr.dataset.codigoBarra,
            descripcion: tr.dataset.descripcion,
            precio: tr.dataset.precio,
            tasa_iva: tr.dataset.tasaIva,
            cantidad: tr.dataset.cantidad,
            stock_minimo: tr.dataset.stockMinimo,
            maneja_stock: tr.dataset.manejaStock,
            activo: tr.dataset.activo
        };

        if (btn.classList.contains('btnEditar')) {
            editarProducto(producto);
        } else {
            cambiarEstado(producto);
        }
    });

    /* ======================================================
       AUTONUMERIC
    ====================================================== */
    new AutoNumeric('#precio', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        currencySymbol: 'Gs. ',
        currencySymbolPlacement: 'p',
        minimumValue: '0'
    });

    new AutoNumeric('#cantidad', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 3,
        minimumValue: '0'
    });
    new AutoNumeric('#stock_minimo', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 3,
        minimumValue: '0'
    });

    /* Mostrar/ocultar campos de stock según "¿Maneja stock?" */
    const chkManejaStock = document.getElementById("maneja_stock");
    const camposStock = document.getElementById("camposStock");
    const hintCantidad = document.getElementById("hintCantidad");

    function toggleCamposStock() {
        const activo = chkManejaStock.checked;
        camposStock.style.display = activo ? "" : "none";
        hintCantidad.style.display = activo ? "" : "none";
    }
    chkManejaStock.addEventListener("change", toggleCamposStock);

    /* Antes de enviar, quitar formato numérico */
    const form = document.getElementById("frmProducto");
    form.addEventListener("submit", function () {
        const precio = AutoNumeric.getAutoNumericElement('#precio');
        if (precio) document.getElementById("precio").value = precio.getNumericString();

        const cantidad = AutoNumeric.getAutoNumericElement('#cantidad');
        if (cantidad) document.getElementById("cantidad").value = cantidad.getNumericString();

        const stockMin = AutoNumeric.getAutoNumericElement('#stock_minimo');
        if (stockMin) document.getElementById("stock_minimo").value = stockMin.getNumericString();
    });

    /* Alerta de éxito auto-cierre */
    document.addEventListener("DOMContentLoaded", function () {
        const alerta = document.getElementById("mensajeSuccess");
        if (alerta) {
            setTimeout(function () {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alerta);
                bsAlert.close();
            }, 3000);
        }
    });

    /* Foco en código de barras al abrir modal */
    const modalProducto = document.getElementById('modalProducto');
    const txtCodigoBarra = document.querySelector('input[name="codigo_barra"]');
    const txtDescripcion = document.querySelector('input[name="descripcion"]');

    modalProducto.addEventListener('shown.bs.modal', function () {
        txtCodigoBarra.focus();
        txtCodigoBarra.select();
    });

    txtCodigoBarra.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            txtDescripcion.focus();
        }
    });

    modalProducto.addEventListener("show.bs.modal", () => {
        if (!modoEdicion) {
            form.reset();
            document.getElementById("pro_cod").value = "";
            document.querySelector(".modal-title").innerText = "Nuevo Producto";
            document.getElementById("cantidad").readOnly = false;
            document.getElementById("maneja_stock").checked = true;
            toggleCamposStock();

            AutoNumeric.getAutoNumericElement("#precio").clear();
            const ca = AutoNumeric.getAutoNumericElement("#cantidad");
            const sm = AutoNumeric.getAutoNumericElement("#stock_minimo");
            if (ca) ca.clear();
            if (sm) sm.clear();
        }
    });

    function editarProducto(producto) {
        modoEdicion = true;
        document.getElementById("pro_cod").value = producto.pro_cod;
        document.querySelector("[name='codigo']").value = producto.codigo ?? "";
        document.querySelector("[name='codigo_barra']").value = producto.codigo_barra;
        document.querySelector("[name='descripcion']").value = producto.descripcion;
        AutoNumeric.getAutoNumericElement("#precio").set(producto.precio);
        AutoNumeric.getAutoNumericElement("#cantidad").set(producto.cantidad ?? 0);
        AutoNumeric.getAutoNumericElement("#stock_minimo").set(producto.stock_minimo ?? 0);
        document.getElementById("tasa_iva").value = producto.tasa_iva ?? "10";
        document.querySelector(".modal-title").innerText = "Editar Producto";
        document.getElementById("maneja_stock").checked = (producto.maneja_stock ?? "S") === "S";
        toggleCamposStock();
        document.getElementById("cantidad").readOnly = true;
        bootstrap.Modal.getOrCreateInstance(modalProducto).show();
    }

    function cambiarEstado(producto) {
        const estadoActual = producto.activo === "S" ? "Activo" : "Inactivo";
        const nuevoEstado = producto.activo === "S" ? "Inactivo" : "Activo";
        confirmar({
            titulo: 'Cambiar estado del producto',
            mensaje: `¿Desea cambiar el estado de "${producto.descripcion}" de ${estadoActual} a ${nuevoEstado}?`,
            acepText: `Sí, pasar a ${nuevoEstado}`,
            acepClase: "btn-primary",
            icono: "fa-arrows-rotate",
            iconoClase: "text-warning"
        }).then(function (ok) {
            if (!ok) {
                return;
            }
            const f = document.createElement("form");
            f.method = "POST";
            f.action = "{{ route('productos.estado') }}";
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "pro_cod";
            input.value = producto.pro_cod;
            f.appendChild(input);
            document.body.appendChild(f);
            f.submit();
        });
    }
</script>
@endsection