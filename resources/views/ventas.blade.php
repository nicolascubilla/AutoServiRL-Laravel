@extends('layouts.app')

@section('title', 'Ventas | Autoservice R &amp; L')

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if (!$caja)
        <div class="alert alert-warning">
            <h5>
                <i class="fas fa-cash-register"></i>
                No hay una caja abierta
            </h5>
            <p class="mb-2">
                Debe abrir una caja antes de realizar
                una venta.
            </p>
            <a
                href="{{ route('caja') }}"
                class="btn btn-primary">
                <i class="fas fa-cash-register"></i>
                Ir a Caja
            </a>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div
                            class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart"></i>
                                Nueva Venta
                            </h5>
                            <span class="badge bg-success">
                                Caja #{{ $caja->caja_id }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label
                                class="form-label">
                                Código de barras
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-barcode"></i>
                                </span>
                                <input
                                    type="text"
                                    id="codigoBarraVenta"
                                    class="form-control form-control-lg"
                                    placeholder="Escanee el código de barras..."
                                    autocomplete="off"
                                    autofocus>
                                <button
                                    type="button"
                                    id="btnBuscarProducto"
                                    class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Escanee el producto utilizando
                                el lector de código de barras.
                            </small>
                        </div>
                        <div
                            id="mensajeVenta"
                            style="display:none;">
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-hover align-middle"
                                id="tablaVenta">
                                <thead>
                                    <tr>
                                        <th>
                                            Producto
                                        </th>
                                        <th
                                            class="text-center"
                                            style="width:120px;">
                                            Cant.
                                        </th>
                                        <th
                                            class="text-end">
                                            Precio
                                        </th>
                                        <th
                                            class="text-end">
                                            Subtotal
                                        </th>
                                        <th
                                            style="width:50px;">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="detalleVenta">
                                </tbody>
                            </table>
                        </div>
                        <div
                            id="ventaVacia"
                            class="text-center text-muted py-5">
                            <i
                                class="fas fa-shopping-cart fa-3x mb-3">
                            </i>
                            <p class="mb-0">
                                Escanee un producto para comenzar.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Resumen
                        </h5>
                    </div>
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between mb-3">
                            <span>
                                Productos
                            </span>
                            <strong id="cantidadProductos">
                                0
                            </strong>
                        </div>
                        <hr>
                        <div
                            class="d-flex justify-content-between align-items-center">
                            <span class="fs-5">
                                TOTAL
                            </span>
                            <strong
                                class="fs-3"
                                id="totalVenta">
                                Gs. 0
                            </strong>
                        </div>
                        <hr>
                        <button
                            type="button"
                            id="btnCobrar"
                            class="btn btn-success btn-lg w-100"
                            disabled>
                            <i class="fas fa-money-bill-wave"></i>
                            Cobrar
                        </button>
                        <button
                            type="button"
                            id="btnCancelarVenta"
                            class="btn btn-outline-danger w-100 mt-2"
                            disabled>
                            <i class="fas fa-trash"></i>
                            Cancelar Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
<div
    class="modal fade"
    id="modalCobrar"
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-money-bill-wave"></i>
                    Cobrar Venta
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <small class="text-muted">
                        Total a pagar
                    </small>
                    <div
                        id="cobroTotal"
                        class="fs-1 fw-bold">
                        Gs. 0
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Forma de pago
                    </label>
                    <select
                        id="formaPago"
                        class="form-select form-select-lg">
                        <option value="E">
                            Efectivo
                        </option>
                        <option value="T">
                            Transferencia
                        </option>
                    </select>
                </div>
                <div
                    id="contenedorEfectivo">
                    <div class="mb-3">
                        <label class="form-label">
                            Monto recibido
                        </label>
                        <input
                            type="text"
                            id="montoRecibido"
                            class="form-control form-control-lg"
                            autocomplete="off">
                    </div>
                    <div
                        class="alert alert-info">
                        <div
                            class="d-flex justify-content-between">
                            <span>
                                Vuelto
                            </span>
                            <strong
                                id="vuelto"
                                class="fs-4">
                                Gs. 0
                            </strong>
                        </div>
                    </div>
                </div>
                <div
                    id="contenedorTransferencia"
                    style="display:none;">
                    <div
                        class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Confirme que la transferencia
                        haya sido recibida antes de
                        finalizar la venta.
                    </div>
                </div>
                <div
                    id="errorCobro"
                    class="alert alert-danger"
                    style="display:none;">
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button
                    type="button"
                    id="btnConfirmarCobro"
                    class="btn btn-success">
                    <i class="fas fa-check"></i>
                    Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>
<div
    class="modal fade"
    id="modalComprobante"
    tabindex="-1"
    data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div
        class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i>
                    Venta registrada
                </h5>
            </div>
            <div class="modal-body text-center">
                <div
                    class="mb-3">
                    <i
                        class="fas fa-check-circle
                               fa-3x
                               text-success">
                    </i>
                </div>
                <h5>
                    Venta registrada correctamente
                </h5>
                <p class="text-muted mb-0">
                    ¿Desea generar una factura
                    para esta venta?
                </p>
            </div>
            <div
                class="modal-footer
                       justify-content-center">
                <button
                    type="button"
                    id="btnGenerarTicket"
                    class="btn btn-secondary">
                    <i class="fas fa-receipt"></i>
                    No, imprimir Ticket
                </button>
                <button
                    type="button"
                    id="btnGenerarFactura"
                    class="btn btn-primary">
                    <i class="fas fa-file-invoice"></i>
                    Sí, generar Factura
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let carrito = [];
    let ventaRegistradaId = null;
    const inputCodigo =
        document.getElementById('codigoBarraVenta');
    const detalleVenta =
        document.getElementById('detalleVenta');
    const ventaVacia =
        document.getElementById('ventaVacia');
    const totalVenta =
        document.getElementById('totalVenta');
    const cantidadProductos =
        document.getElementById('cantidadProductos');
    const btnCobrar =
        document.getElementById('btnCobrar');
    const btnCancelarVenta =
        document.getElementById('btnCancelarVenta');
    const mensajeVenta =
        document.getElementById('mensajeVenta');
    function formatoGs(valor) {
        return 'Gs. ' +
            Number(valor).toLocaleString('es-PY');
    }
    function mostrarMensaje(mensaje, tipo = 'danger') {
        mensajeVenta.innerHTML = `
        <div class="alert alert-${tipo}">
            ${mensaje}
        </div>
    `;
        mensajeVenta.style.display = 'block';
        setTimeout(function() {
            mensajeVenta.style.display = 'none';

        }, 3000);
    }
    async function buscarProducto() {
        const codigo = inputCodigo.value.trim();
        if (codigo === '') {
            return;
        }
        try {
            const respuesta = await fetch(
                '{{ route("ventas.buscar_barra") }}?codigo_barra=' +
                encodeURIComponent(codigo)
            );
            const data = await respuesta.json();
            if (!data.success) {
                mostrarMensaje(
                    data.mensaje,
                    'danger'
                );
                inputCodigo.value = '';
                inputCodigo.focus();
                return;
            }
            agregarProducto(data.producto);
            inputCodigo.value = '';
            inputCodigo.focus();
        } catch (error) {
            console.error(error);
            mostrarMensaje(
                'Ocurrió un error al buscar el producto.',
                'danger'
            );
            inputCodigo.focus();
        }
    }
    function agregarProducto(producto) {
        const existente =
            carrito.find(
                item =>
                Number(item.pro_cod) ===
                Number(producto.pro_cod)
            );
        if (existente) {
            existente.cantidad++;
            existente.subtotal =
                existente.cantidad *
                Number(existente.precio);
        } else {
            carrito.push({
                pro_cod: producto.pro_cod,
                codigo_barra: producto.codigo_barra,
                descripcion: producto.descripcion,
                precio: Number(producto.precio),
                cantidad: 1,
                subtotal: Number(producto.precio)
            });
        }
        renderizarCarrito();
    }
    function renderizarCarrito() {
        detalleVenta.innerHTML = '';
        if (carrito.length === 0) {
            ventaVacia.style.display = 'block';
            btnCobrar.disabled = true;
            btnCancelarVenta.disabled = true;
            totalVenta.textContent =
                formatoGs(0);
            cantidadProductos.textContent = '0';
            return;
        }
        ventaVacia.style.display = 'none';
        btnCobrar.disabled = false;
        btnCancelarVenta.disabled = false;
        let total = 0;
        let cantidadTotal = 0;
        carrito.forEach(function(producto, index) {
            total += producto.subtotal;
            cantidadTotal += producto.cantidad;
            const fila =
                document.createElement('tr');
            fila.innerHTML = `
            <td>
                <strong>
                    ${producto.descripcion}
                </strong>
                <br>
                <small class="text-muted">
                    ${producto.codigo_barra}
                </small>
            </td>
            <td class="text-center">
                <div
                    class="input-group input-group-sm">
                    <button
                        class="btn btn-outline-secondary"
                        onclick="disminuirCantidad(${index})">
                        -
                    </button>
                    <input
                        type="text"
                        class="form-control text-center"
                        value="${producto.cantidad}"
                        readonly>
                    <button
                        class="btn btn-outline-secondary"
                        onclick="aumentarCantidad(${index})">
                        +
                    </button>
                </div>
            </td>
            <td class="text-end">
                ${formatoGs(producto.precio)}
            </td>
            <td class="text-end">
                <strong>
                    ${formatoGs(producto.subtotal)}
                </strong>
            </td>
            <td>
                <button
                    class="btn btn-outline-danger btn-sm"
                    onclick="eliminarProducto(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
            detalleVenta.appendChild(fila);
        });
        totalVenta.textContent =
            formatoGs(total);
        cantidadProductos.textContent =
            cantidadTotal;
    }
    function aumentarCantidad(index) {
        const producto = carrito[index];
        producto.cantidad++;
        producto.subtotal =
            producto.cantidad *
            producto.precio;
        renderizarCarrito();
        inputCodigo.focus();
    }
    function disminuirCantidad(index) {
        const producto = carrito[index];
        if (producto.cantidad <= 1) {
            eliminarProducto(index);
            return;
        }
        producto.cantidad--;
        producto.subtotal =
            producto.cantidad *
            producto.precio;
        renderizarCarrito();
        inputCodigo.focus();
    }
    function eliminarProducto(index) {
        carrito.splice(index, 1);
        renderizarCarrito();
        inputCodigo.focus();
    }
    inputCodigo.addEventListener(
        'keydown',
        function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProducto();
            }
        }
    );
    document
        .getElementById('btnBuscarProducto')
        .addEventListener(
            'click',
            buscarProducto
        );
    btnCancelarVenta.addEventListener(
        'click',
        async function() {
            if (carrito.length === 0) {
                return;
            }
            const ok = await confirmar({
                titulo: 'Cancelar venta',
                mensaje: '¿Desea descartar la venta actual? Se eliminarán todos los artículos del carrito.',
                acepText: 'Sí, cancelar venta',
                icono: 'fa-trash',
                iconoClase: 'text-danger'
            });
            if (!ok) {
                return;
            }
            carrito = [];
            renderizarCarrito();
            inputCodigo.focus();
        }
    );
    btnCobrar.addEventListener(
        'click',
        function() {
            if (carrito.length === 0) {
                return;
            }
            const total = carrito.reduce(
                (suma, item) =>
                suma + item.subtotal,
                0
            );
            document.getElementById(
                'cobroTotal'
            ).textContent = formatoGs(total);
            document.getElementById(
                'montoRecibido'
            ).value = '';
            document.getElementById(
                'vuelto'
            ).textContent = formatoGs(0);
            document.getElementById(
                'errorCobro'
            ).style.display = 'none';
            const modal =
                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById(
                        'modalCobrar'
                    )
                );
            modal.show();
        }
    );
    const formaPago =
        document.getElementById('formaPago');
    const contenedorEfectivo =
        document.getElementById(
            'contenedorEfectivo'
        );
    const contenedorTransferencia =
        document.getElementById(
            'contenedorTransferencia'
        );
    formaPago.addEventListener(
        'change',
        function() {
            if (this.value === 'E') {
                contenedorEfectivo.style.display =
                    'block';
                contenedorTransferencia.style.display =
                    'none';
                setTimeout(function() {
                    resaltarCampoMonto(true);
                    document
                        .getElementById(
                            'montoRecibido'
                        )
                        .focus({ preventScroll: true });
                }, 100);
            } else {
                contenedorEfectivo.style.display =
                    'none';
                contenedorTransferencia.style.display =
                    'block';
            }
        }
    );
    const montoRecibido =
        document.getElementById(
            'montoRecibido'
        );
    document.getElementById('modalCobrar').addEventListener(
        'shown.bs.modal',
        function() {
            if (formaPago.value === 'E') {
                resaltarCampoMonto(true);
                montoRecibido.focus({ preventScroll: true });
                montoRecibido.select();
            }
        }
    );
    function resaltarCampoMonto(activar) {
        if (activar) {
            montoRecibido.classList.add('is-valid');
        } else {
            montoRecibido.classList.remove('is-valid');
        }
    }
    montoRecibido.addEventListener(
        'input',
        function() {
            let recibido =
                this.value.replace(
                    /[^0-9]/g,
                    ''
                );
            recibido =
                Number(recibido || 0);
            const total =
                carrito.reduce(
                    (suma, item) =>
                    suma + item.subtotal,
                    0
                );
            const vuelto =
                recibido - total;
            document.getElementById(
                    'vuelto'
                ).textContent =
                formatoGs(
                    vuelto > 0 ?
                    vuelto :
                    0
                );
            this.value =
                recibido.toLocaleString(
                    'es-PY'
                );
        }
    );
    document
        .getElementById('btnConfirmarCobro')
        .addEventListener(
            'click',
            async function() {
                const boton = this;
                const forma =
                    formaPago.value;
                const total =
                    carrito.reduce(
                        (suma, item) =>
                        suma + item.subtotal,
                        0
                    );
                const error =
                    document.getElementById(
                        'errorCobro'
                    );
                error.style.display =
                    'none';
                let recibido = 0;
                if (forma === 'E') {
                    recibido =
                        Number(
                            montoRecibido.value
                            .replace(
                                /[^0-9]/g,
                                ''
                            ) || 0
                        );
                    if (recibido < total) {
                        error.textContent =
                            'El monto recibido es menor al total de la venta.';
                        error.style.display =
                            'block';
                        montoRecibido.focus();
                        return;
                    }
                }
                boton.disabled = true;
                boton.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm">
                </span>
                Procesando...
            `;
                try {
                    const respuesta =
                        await fetch(
                            '{{ route("venta.guardar") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    carrito: carrito,
                                    forma_pago: forma,
                                    monto_recibido: recibido
                                })
                            }
                        );
                    const data =
                        await respuesta.json();
                    if (!data.success) {
                        throw new Error(
                            data.mensaje ||
                            'No fue posible registrar la venta.'
                        );
                    }
                    console.log(
                        'Venta registrada:',
                        data
                    );
                    ventaRegistradaId =
                        data.venta_id;
                    const modalCobrar =
                        bootstrap.Modal.getInstance(
                            document.getElementById(
                                'modalCobrar'
                            )
                        );
                    if (modalCobrar) {
                        modalCobrar.hide();
                    }
                    const modalComprobante =
                        bootstrap.Modal.getOrCreateInstance(
                            document.getElementById(
                                'modalComprobante'
                            )
                        );

                    modalComprobante.show();
                } catch (error) {
                    console.error(error);
                    document.getElementById(
                            'errorCobro'
                        ).textContent =
                        error.message;
                    document.getElementById(
                            'errorCobro'
                        ).style.display =
                        'block';
                    boton.disabled = false;
                    boton.innerHTML = `
                    <i class="fas fa-check"></i>
                    Confirmar Pago
                `;
                }
            }
        );

document
    .getElementById(
        'btnGenerarTicket'
    )
    .addEventListener(
        'click',
        function() {

            if (!ventaRegistradaId) {

                return;

            }


            window.location.href =
                '{{ route("ticket", ["venta" => "__ID__"]) }}'.replace('__ID__', ventaRegistradaId);

        }
    );

document
    .getElementById(
        'btnGenerarFactura'
    )
    .addEventListener(
        'click',
        function() {

            if (!ventaRegistradaId) {

                return;

            }


            window.location.href =
                '{{ route("facturacion", ["venta" => "__ID__"]) }}'.replace('__ID__', ventaRegistradaId);

        }
    );
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            if (inputCodigo) {
                inputCodigo.focus();
            }
        }
    );
</script>
@endsection
