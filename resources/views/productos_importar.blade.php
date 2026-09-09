@extends('layouts.app')

@section('title', 'Importar Productos | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @include('partials.flash')

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-import me-2 text-primary"></i>Importar Productos
            </h2>
            <p class="text-muted mb-0">
                Cargue el catálogo de productos desde un archivo Excel (.xlsx).
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('productos') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Productos
            </a>
            <a href="{{ route('productos') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </a>
        </div>
    </div>

    @if (is_array(session('import_resultado')))
        @php $resultado = session('import_resultado'); @endphp
        @if ($resultado['errores'] === null)
            <div id="mensajeError" class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>No se pudo completar la importación.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @else
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Resultado de la importación</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                            <i class="fas fa-plus me-1"></i>{{ $resultado['insertadas'] }} insertado(s)
                        </span>
                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary">
                            <i class="fas fa-rotate me-1"></i>{{ $resultado['actualizadas'] }} actualizado(s)
                        </span>
                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ count($resultado['errores']) }} con error(es)
                        </span>
                    </div>

                    @if (count($resultado['errores']) > 0)
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading mb-2"><i class="fas fa-list me-1"></i>Errores detectados (las filas no fueron procesadas):</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($resultado['errores'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    @isset($resumen)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Vista previa</h5>
                <p class="text-muted small">
                    Nada se guarda todavía: revise el resultado y confirme para importar.
                    Los productos que ya existen por <strong>código de barra</strong> se actualizarán.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                        <i class="fas fa-plus me-1"></i>{{ $resumen['insertar'] }} a insertar
                    </span>
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary">
                        <i class="fas fa-rotate me-1"></i>{{ $resumen['actualizar'] }} a actualizar
                    </span>
                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $resumen['error'] }} con errores
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Fila</th>
                                <th>Código de barra</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-end">Precio</th>
                                <th>IVA</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filas as $fila)
                                <tr>
                                    <td class="text-muted">{{ $fila['fila'] }}</td>
                                    <td>{{ $fila['codigo_barra'] }}</td>
                                    <td>{{ $fila['codigo'] ?? '-' }}</td>
                                    <td>{{ $fila['descripcion'] }}</td>
                                    <td class="text-end">
                                        {{ $fila['precio'] !== null ? 'Gs. ' . number_format($fila['precio'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td>{{ $fila['tasa_iva'] === 'E' ? 'Exento' : ($fila['tasa_iva'] . '%') }}</td>
                                    <td class="text-end">{{ $fila['cantidad'] !== null ? number_format($fila['cantidad'], 0, ',', '.') : '-' }}</td>
                                    <td class="text-end">{{ number_format($fila['stock_minimo'], 0, ',', '.') }}</td>
                                    <td>
                                        @if ($fila['accion'] === 'insertar')
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success">Nuevo</span>
                                        @elseif ($fila['accion'] === 'actualizar')
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary">Actualizar</span>
                                        @else
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger" title="{{ $fila['mensaje'] }}">Con error</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($resumen['error'] > 0)
                    <div class="alert alert-warning small mt-2 mb-3">
                        <strong>Filas con error:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($filas as $fila)
                                @if ($fila['accion'] === 'error')
                                    <li>Fila {{ $fila['fila'] }}: {{ $fila['mensaje'] }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('productos.importar.ejecutar') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Confirmar importación
                        </button>
                    </form>
                    <a href="{{ route('productos.importar') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-upload me-2 text-primary"></i>Subir archivo</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('productos.importar.vista') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo Excel (.xlsx)</label>
                                <input type="file" name="archivo" id="archivo" class="form-control"
                                    accept=".xlsx" required>
                                <div class="form-text">Máximo 10 MB. Solo se aceptan archivos .xlsx.</div>
                                @error('archivo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Previsualizar importación
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-list-ol me-2 text-primary"></i>Formato esperado</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">La primera fila debe contener los encabezados (en cualquier orden).</p>
                        <table class="table table-sm table-borderless mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Columna</th>
                                    <th>Ejemplo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Código de barra <span class="text-danger">*</span></td><td>7798183000015</td></tr>
                                <tr><td>Código</td><td>P-001</td></tr>
                                <tr><td>Descripción <span class="text-danger">*</span></td><td>Aceite 20W50 1L</td></tr>
                                <tr><td>Precio <span class="text-danger">*</span></td><td>25000</td></tr>
                                <tr><td>IVA</td><td>E / 5 / 10</td></tr>
                                <tr><td>Cantidad</td><td>50</td></tr>
                                <tr><td>Stock mínimo</td><td>10</td></tr>
                            </tbody>
                        </table>
                        <ul class="mb-0 text-muted small">
                            <li><span class="text-danger">*</span> Obligatorio.</li>
                            <li>Si el código de barra ya existe, el producto se <strong>actualiza</strong> (precio, IVA, descripción y stock).</li>
                            <li>Si la celda de cantidad está vacía, se mantiene/comienza en 0.</li>
                            <li>El IVA se interpreta como E (exento), 5 o 10. Las celdas en porcentaje (5%, 10%) también se aceptan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endisset

</div>
@endsection