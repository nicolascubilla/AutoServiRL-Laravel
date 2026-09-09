@extends('layouts.app')

@section('title', 'Productos | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <!-- ENCABEZADO -->
    <div class="page-header">
        <div>
            <h2 class="mb-0">Productos</h2>
            <p class="text-muted mb-0">Gestione el catálogo de productos del negocio</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('productos.importar') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-import me-1"></i> Importar Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- TOOLBAR DE TABLA -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="buscarProducto" class="form-control" placeholder="Código, barras o descripción...">
                    </div>
                </div>
                <div class="col-md-7 col-lg-8 text-md-end text-muted small" id="totalRegistros"></div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div id="tablaProductos"></div>
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

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label">Cantidad inicial <span class="text-danger">*</span></label>
                            <input type="text" id="cantidad" name="cantidad" class="form-control" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label">Stock mínimo</label>
                            <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" autocomplete="off">
                        </div>
                    </div>
                </div>
                <small class="text-muted d-block mt-1">
                    La cantidad inicial solo aplica al crear el producto. Para modificarla luego, use el módulo de Stock.
                </small>
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
     * Los productos llegan directamente desde el servidor vía PHP.
     * La paginación y la búsqueda son locales (client-side), por lo que
     * el manejo de registros es fluido y no depende de peticiones extra.
     */
    const productos = @json($productos);
    const table = new Tabulator("#tablaProductos", {
        data: productos,
        index: "pro_cod",
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No existen productos registrados.",
        height: "calc(100vh - 320px)",
        minHeight: 300,
        pagination: true,
        paginationSize: 15,
        paginationSizeSelector: [15, 30, 50, 100, 200],
        paginationCounter: "rows",
        paginationButtonCount: 5,
        movableColumns: false,
        columns: [{
                title: "ID",
                field: "pro_cod",
                width: 70,
                hozAlign: "center",
                headerSort: true
            },
            {
                title: "Código",
                field: "codigo",
                headerSort: true
            },
            {
                title: "Código Barra",
                field: "codigo_barra",
                headerSort: true
            },
            {
                title: "Descripción",
                field: "descripcion",
                headerSort: true,
                minWidth: 200
            },
            {
                title: "Precio",
                field: "precio",
                width: 140,
                hozAlign: "right",
                headerSort: true,
                formatter: function(cell) {
                    return "Gs. " + Number(cell.getValue() || 0)
                        .toLocaleString("es-PY");
                }
            },
            {
                title: "IVA",
                field: "tasa_iva",
                width: 100,
                hozAlign: "center",
                formatter: function(cell) {
                    return cell.getValue() === "E" ? "Exenta" : cell.getValue() + "%";
                }
            },
            {
                title: "Estado",
                field: "activo",
                width: 110,
                hozAlign: "center",
                formatter: function(cell) {
                    return cell.getValue() == "S" ?
                        "<span class='badge rounded-pill bg-success-subtle text-success border border-success'>Activo</span>" :
                        "<span class='badge rounded-pill bg-danger-subtle text-danger border border-danger'>Inactivo</span>";
                }
            },
            {
                title: "Acciones",
                width: 130,
                hozAlign: "center",
                widthShrink: 2,
                formatter: function(cell) {
                    const p = cell.getRow().getData();
                    const esActivo = p.activo === "S";
                    return `<div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-warning btnEditar" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn ${esActivo ? 'btn-outline-danger' : 'btn-outline-success'} btnEstado" title="${esActivo ? 'Desactivar' : 'Activar'}">
                            <i class="fas ${esActivo ? 'fa-power-off' : 'fa-check'}"></i>
                        </button>
                    </div>`;
                },
                cellClick: function(e, cell) {
                    const producto = cell.getRow().getData();
                    if (e.target.closest(".btnEditar")) {
                        editarProducto(producto);
                    }
                    if (e.target.closest(".btnEstado")) {
                        cambiarEstado(producto);
                    }
                }
            }
        ]
    });

    /* Contador de registros visibles tras filtrar */
    function actualizarContador() {
        const count = table.getDataCount();
        document.getElementById("totalRegistros").innerHTML =
            count + " registro" + (count === 1 ? "" : "s");
    }
    table.on("dataFiltered", actualizarContador);
    actualizarContador();

    /*
     * Búsqueda local con debounce: filtra en memoria sin recargar el servidor.
     * Con muchos registros sigue siendo fluido porque no hay round-trips.
     */
    const buscar = document.getElementById("buscarProducto");
    buscar.addEventListener("keyup", function() {
        clearTimeout(timerBusqueda);
        const valor = this.value.trim().toLowerCase();
        timerBusqueda = setTimeout(function() {
            if (valor === "") {
                table.clearFilter();
            } else {
                table.setFilter(function(data) {
                    return (
                        (data.descripcion ?? "").toLowerCase().includes(valor) ||
                        (data.codigo ?? "").toLowerCase().includes(valor) ||
                        (data.codigo_barra ?? "").toLowerCase().includes(valor)
                    );
                });
            }
        }, 300);
    });

    /* AutoNumeric para el precio */
    new AutoNumeric('#precio', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        currencySymbol: 'Gs. ',
        currencySymbolPlacement: 'p',
        minimumValue: '0'
    });

    /* AutoNumeric para cantidad y stock mínimo */
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

    /* Antes de enviar, quitar formato numérico */
    const form = document.getElementById("frmProducto");
    form.addEventListener("submit", function() {
        const precio = AutoNumeric.getAutoNumericElement('#precio');
        if (precio) document.getElementById("precio").value = precio.getNumericString();

        const cantidad = AutoNumeric.getAutoNumericElement('#cantidad');
        if (cantidad) document.getElementById("cantidad").value = cantidad.getNumericString();

        const stockMin = AutoNumeric.getAutoNumericElement('#stock_minimo');
        if (stockMin) document.getElementById("stock_minimo").value = stockMin.getNumericString();
    });

    /* Alerta de éxito auto-cierre */
    document.addEventListener("DOMContentLoaded", function() {
        const alerta = document.getElementById("mensajeSuccess");
        if (alerta) {
            setTimeout(function() {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alerta);
                bsAlert.close();
            }, 3000);
        }
    });

    /* Foco en código de barras al abrir modal */
    const modalProducto = document.getElementById('modalProducto');
    const txtCodigoBarra = document.querySelector('input[name="codigo_barra"]');
    const txtDescripcion = document.querySelector('input[name="descripcion"]');

    modalProducto.addEventListener('shown.bs.modal', function() {
        txtCodigoBarra.focus();
        txtCodigoBarra.select();
    });

    txtCodigoBarra.addEventListener("keydown", function(e) {
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
        document.getElementById("cantidad").readOnly = true;
        bootstrap.Modal.getOrCreateInstance(modalProducto).show();
    }

    async function cambiarEstado(producto) {
        const estadoActual = producto.activo === "S" ? "Activo" : "Inactivo";
        const nuevoEstado = producto.activo === "S" ? "Inactivo" : "Activo";
        const ok = await confirmar({
            titulo: `Cambiar estado del producto`,
            mensaje: `¿Desea cambiar el estado de "${producto.descripcion}" de ${estadoActual} a ${nuevoEstado}?`,
            acepText: `Sí, pasar a ${nuevoEstado}`,
            acepClase: "btn-primary",
            icono: "fa-arrows-rotate",
            iconoClase: "text-warning"
        });
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
    }
</script>
@endsection