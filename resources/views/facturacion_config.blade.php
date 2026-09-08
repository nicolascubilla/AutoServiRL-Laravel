@extends('layouts.app')

@section('title', 'Configuración de Facturación | Autoservice R & L')

@section('content')
@php
$hoy = date('Y-m-d');
$fin = $configuracion['fecha_fin_vigencia'] ?? null;
$ini = $configuracion['fecha_inicio_vigencia'] ?? null;
$iniInput = $ini ? date('Y-m-d', strtotime((string) $ini)) : '';
$finInput = $fin ? date('Y-m-d', strtotime((string) $fin)) : '';

if ($fin && $ini && $hoy >= $ini && $hoy <= $fin) {
    $estadoTimbrado = ['tipo' => 'vigente', 'texto' => 'Vigente', 'icono' => 'fa-check-circle', 'color' => 'success'];
} elseif ($fin && $fin < $hoy) {
    $estadoTimbrado = ['tipo' => 'vencido', 'texto' => 'Vencido', 'icono' => 'fa-times-circle', 'color' => 'danger'];
} else {
    $estadoTimbrado = ['tipo' => 'proximo', 'texto' => 'Próximo a iniciar', 'icono' => 'fa-hourglass-half', 'color' => 'warning'];
}

$siguienteNumero = ((int) $configuracion['ultimo_numero_factura']) + 1;
$numeroFacturaPreview =
    str_pad($configuracion['establecimiento'], 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($configuracion['punto_expedicion'], 3, '0', STR_PAD_LEFT)
    . '-' . str_pad($siguienteNumero, 7, '0', STR_PAD_LEFT);
@endphp

<div class="container-fluid">

    @if (session('success'))
        <div id="mensajeSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (session('error'))
        <div id="mensajeError" class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Configuración de Facturación
            </h2>
            <p class="text-muted">
                Datos utilizados para la emisión de sus comprobantes fiscales (facturas).
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    @if (empty($configuracion))

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No existe una configuración de facturación.
        </div>

    @else

    <form method="POST" action="{{ route('facturacion.config.actualizar') }}">

        @csrf

        <input type="hidden" name="config_id" value="{{ $configuracion['config_id'] }}">

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                        <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                            <i class="fas fa-store"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Datos del Negocio</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nombre del negocio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-shop"></i></span>
                                    <input type="text" name="nombre_negocio" class="form-control" required
                                        value="{{ $configuracion['nombre_negocio'] }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Propietario / Razón social</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" name="propietario" class="form-control"
                                        value="{{ $configuracion['propietario'] ?? '' }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Actividad comercial</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <input type="text" name="actividad" class="form-control"
                                        value="{{ $configuracion['actividad'] ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">RUC <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="ruc" class="form-control" required
                                        value="{{ $configuracion['ruc'] }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="telefono" class="form-control"
                                        value="{{ $configuracion['telefono'] ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Ciudad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-city"></i></span>
                                    <input type="text" name="ciudad" class="form-control"
                                        value="{{ $configuracion['ciudad'] ?? '' }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Dirección</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-location-dot"></i></span>
                                    <input type="text" name="direccion" class="form-control"
                                        value="{{ $configuracion['direccion'] ?? '' }}">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                        <div class="rounded-3 p-2 bg-secondary-subtle text-secondary">
                            <i class="fas fa-stamp"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Timbrado</h5>
                        <span class="badge rounded-pill text-bg-{{ $estadoTimbrado['color'] }} ms-auto">
                            <i class="fas {{ $estadoTimbrado['icono'] }} me-1"></i>{{ $estadoTimbrado['texto'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Número de timbrado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="timbrado" class="form-control" required
                                        value="{{ $configuracion['timbrado'] }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Fecha inicio de vigencia <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <input type="date" name="fecha_inicio_vigencia" class="form-control" required
                                        value="{{ $iniInput }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Fecha fin de vigencia <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                                    <input type="date" name="fecha_fin_vigencia" class="form-control" required
                                        value="{{ $finInput }}">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                        <div class="rounded-3 p-2 bg-info-subtle text-info">
                            <i class="fas fa-sort-numeric-up-alt"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Numeración de Factura</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Establecimiento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" name="establecimiento" class="form-control" maxlength="3" required
                                        value="{{ $configuracion['establecimiento'] }}">
                                </div>
                                <div class="form-text">Ejemplo: 001</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Punto de expedición <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-flag-checkered"></i></span>
                                    <input type="text" name="punto_expedicion" class="form-control" maxlength="3" required
                                        value="{{ $configuracion['punto_expedicion'] }}">
                                </div>
                                <div class="form-text">Ejemplo: 002</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Último número utilizado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                                    <input type="number" name="ultimo_numero_factura" class="form-control" min="0" required
                                        value="{{ $configuracion['ultimo_numero_factura'] }}">
                                </div>
                                <div class="form-text">El sistema generará el siguiente número automáticamente.</div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card shadow-sm border-0 mb-4 sticky-lg-top">
                    <div class="card-header bg-white d-flex align-items-center gap-2 py-3">
                        <div class="rounded-3 p-2 bg-success-subtle text-success">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Vista previa</h5>
                    </div>
                    <div class="card-body">

                        <label class="form-label fw-semibold small text-muted">Próximo número de factura</label>
                        <div class="text-center py-3 px-2 rounded-3 bg-light border mb-3">
                            <span class="text-muted small d-block mb-1">Factura N°</span>
                            <span class="font-monospace fs-5 fw-bold text-primary" style="letter-spacing:1px;">
                                {{ $numeroFacturaPreview }}
                            </span>
                        </div>

                        <div class="vstack gap-2 small">
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">Nombre:</span>
                                <span class="fw-semibold text-end">{{ $configuracion['nombre_negocio'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">RUC:</span>
                                <span class="fw-semibold">{{ $configuracion['ruc'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">Timbrado:</span>
                                <span class="fw-semibold">{{ $configuracion['timbrado'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">Vigencia:</span>
                                <span class="fw-semibold">{{ $ini }} &rarr; {{ $fin }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Estado:</span>
                                <span class="badge rounded-pill text-bg-{{ $estadoTimbrado['color'] }}">
                                    <i class="fas {{ $estadoTimbrado['icono'] }} me-1"></i>{{ $estadoTimbrado['texto'] }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('dashboard') }}" class="btn btn-light me-2">
                <i class="fas fa-times me-1"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Guardar Configuración
            </button>
        </div>

    </form>

    @endif

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
