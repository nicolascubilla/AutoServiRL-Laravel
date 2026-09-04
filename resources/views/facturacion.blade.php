@extends('layouts.app')

@section('title', 'Facturación de Venta | AutoServiRL')

@section('content')
@php
$venta = $venta ?? (object) ['venta_id' => 0, 'total' => 0];
$detalle = $detalle ?? [];
$totales = $totales ?? [
    'total_exenta' => 0,
    'total_gravada_5' => 0,
    'total_gravada_10' => 0,
    'total_iva_5' => 0,
    'total_iva_10' => 0,
    'total_iva' => 0,
    'total' => 0
];
@endphp
<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Facturación de Venta
            </h2>
            <p class="text-muted">
                Emita la factura de la venta <strong>#{{ (int)$venta->venta_id }}</strong> buscando al cliente por RUC.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ventas') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="mb-0 fw-semibold">Datos del Cliente</h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">RUC / C.I. <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" id="clienteRuc" class="form-control" placeholder="Ej: 6169646-3" autocomplete="off">
                            <button type="button" id="btnBuscarRuc" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                        </div>
                        <div class="form-text">Escriba el RUC y presione Enter o Buscar.</div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Nombre o Razón Social <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                            <input type="text" id="clienteNombre" class="form-control" placeholder="Nombre o razón social" autocomplete="off">
                        </div>
                    </div>

                    <div id="mensajeCliente" class="mt-3" style="display:none;"></div>
                </div>
            </div>

        </div>

        <div class="col-lg-7">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                    <div class="rounded-3 p-2 bg-success-subtle text-success">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h5 class="mb-0 fw-semibold">Venta #{{ (int)$venta->venta_id }}</h5>
                    <div class="ms-auto text-end">
                        <div class="text-muted small">Total de la venta</div>
                        <div class="fw-bold text-success fs-5">Gs. {{ number_format($venta->total, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="fw-semibold text-muted section-title mb-3">Productos</h6>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Cant.</th>
                                    <th>Descripción</th>
                                    <th>IVA</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detalle as $item)
                                    <tr>
                                        <td class="text-center">{{ (float)$item->cantidad }}</td>
                                        <td>{{ $item->descripcion }}</td>
                                        <td>
                                            @if ($item->tasa_iva === 'E')
                                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary">Exenta</span>
                                            @else
                                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary">IVA {{ (int)$item->tasa_iva }}%</span>
                                            @endif
                                        </td>
                                        <td class="text-end">Gs. {{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">Gs. {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="rounded-3 border p-3 text-center h-100">
                                <div class="text-muted small fw-semibold">Total Exenta</div>
                                <div class="fw-bold fs-6">Gs. {{ number_format($totales['total_exenta'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="rounded-3 border p-3 text-center h-100">
                                <div class="text-muted small fw-semibold">Base IVA 5%</div>
                                <div class="fw-bold fs-6">Gs. {{ number_format($totales['total_gravada_5'], 0, ',', '.') }}</div>
                                <div class="small text-primary">IVA: Gs. {{ number_format($totales['total_iva_5'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="rounded-3 border p-3 text-center h-100">
                                <div class="text-muted small fw-semibold">Base IVA 10%</div>
                                <div class="fw-bold fs-6">Gs. {{ number_format($totales['total_gravada_10'], 0, ',', '.') }}</div>
                                <div class="small text-primary">IVA: Gs. {{ number_format($totales['total_iva_10'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Total IVA</span>
                            <span class="fs-5 fw-bold">Gs. {{ number_format($totales['total_iva'], 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Total Factura</span>
                            <span class="fs-3 fw-bold text-success">Gs. {{ number_format($totales['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-end flex-wrap gap-2">
                    <a href="{{ route('ventas') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <button type="button" id="btnEmitirFactura" class="btn btn-success px-4">
                        <i class="fas fa-file-invoice me-1"></i> Emitir Factura
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
@endsection

@section('scripts')
<script>
    const clienteRuc = document.getElementById('clienteRuc');
    const clienteNombre = document.getElementById('clienteNombre');
    const btnBuscarRuc = document.getElementById('btnBuscarRuc');
    const mensajeCliente = document.getElementById('mensajeCliente');
    const btnEmitirFactura = document.getElementById('btnEmitirFactura');

    const ventaId = {{ (int) $venta->venta_id }};

    function mostrarMensajeCliente(mensaje, tipo = 'danger') {
        mensajeCliente.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show mb-0">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        `;
        mensajeCliente.style.display = 'block';
        setTimeout(function() { mensajeCliente.style.display = 'none'; }, 4000);
    }

    async function buscarClientePorRuc() {
        const ruc = clienteRuc.value.trim();
        if (ruc === '') {
            mostrarMensajeCliente('Ingrese un RUC para realizar la búsqueda.');
            clienteRuc.focus();
            return;
        }
        clienteNombre.value = '';
        btnBuscarRuc.disabled = true;
        btnBuscarRuc.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Buscando...`;
        try {
            const respuesta = await fetch('{{ route("cliente.buscar_ruc") }}?ruc=' + encodeURIComponent(ruc));
            const data = await respuesta.json();
            if (!data.success) {
                throw new Error(data.mensaje || 'No se encontró información para el RUC ingresado.');
            }
            clienteRuc.value = data.datos.fullRuc;
            clienteNombre.value = data.datos.name;
            mostrarMensajeCliente('Cliente encontrado correctamente.', 'success');
            clienteNombre.focus();
        } catch (error) {
            console.error(error);
            mostrarMensajeCliente(error.message, 'danger');
            clienteRuc.focus();
        } finally {
            btnBuscarRuc.disabled = false;
            btnBuscarRuc.innerHTML = `<i class="fas fa-search me-1"></i> Buscar`;
        }
    }

    btnBuscarRuc.addEventListener('click', buscarClientePorRuc);

    clienteRuc.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarClientePorRuc();
        }
    });

    btnEmitirFactura.addEventListener('click', async function() {
        const ruc = clienteRuc.value.trim();
        const nombre = clienteNombre.value.trim();

        if (ruc === '') {
            mostrarMensajeCliente('Debe ingresar el RUC o C.I. del cliente.');
            clienteRuc.focus();
            return;
        }
        if (nombre === '') {
            mostrarMensajeCliente('Debe ingresar el nombre o razón social del cliente.');
            clienteNombre.focus();
            return;
        }

        const boton = this;
        boton.disabled = true;
        boton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Emitiendo...`;

        try {
            const respuesta = await fetch('{{ route("factura.emitir") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.getElementById('csrfToken').value
                },
                body: JSON.stringify({
                    venta_id: ventaId,
                    cliente_documento: ruc,
                    cliente_nombre: nombre
                })
            });
            const data = await respuesta.json();

            if (!data.success) {
                throw new Error(data.mensaje || 'No fue posible emitir la factura.');
            }

            console.log('Factura generada:', data);

            if (!data.factura_id) {
                throw new Error('La factura fue generada, pero no se recibió su identificador.');
            }

            window.open('{{ route("factura.imprimir", ["factura" => "__ID__"]) }}'.replace('__ID__', data.factura_id), '_blank');
            window.location.href = '{{ route("ventas") }}';
        } catch (error) {
            console.error(error);
            mostrarMensajeCliente(error.message);
            boton.disabled = false;
            boton.innerHTML = `<i class="fas fa-file-invoice me-1"></i> Emitir Factura`;
        }
    });
</script>
@endsection
