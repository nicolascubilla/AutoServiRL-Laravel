@extends('layouts.app')

@section('title', 'Caja | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div id="mensajeSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div id="mensajeError" class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-cash-register me-2"></i>Caja</h2>
            <p class="text-muted mb-0">
                @if($cajaActual)
                    Caja abierta #{{ $cajaActual->caja_id }}
                @else
                    No hay caja abierta actualmente
                @endif
            </p>
        </div>
        @if($cajaActual)
            <span class="badge rounded-pill bg-success fs-6 px-3 py-2">
                <i class="fas fa-circle me-1"></i> ABIERTA
            </span>
        @else
            <span class="badge rounded-pill bg-secondary fs-6 px-3 py-2">
                <i class="fas fa-circle me-1"></i> CERRADA
            </span>
        @endif
    </div>

    @if(!$cajaActual)

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="state-icon bg-secondary-subtle text-secondary mx-auto mb-3">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h5 class="mb-1 fw-bold">Abrir Caja</h5>
                            <p class="text-muted mb-0">
                                Registre el dinero disponible para iniciar la jornada
                            </p>
                        </div>

                        <form method="POST" action="{{ route('caja.abrir') }}" id="frmAbrirCaja">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Monto inicial <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-muted"></i></span>
                                    <input type="text" id="monto_inicial" name="monto_inicial" class="form-control" autocomplete="off" required>
                                </div>
                                <small class="text-muted">Dinero disponible al iniciar la jornada</small>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-lock-open me-1"></i> Abrir Caja
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @else

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="summary-icon bg-info-subtle text-info">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Usuario</p>
                            <h5 class="mb-1 fw-semibold">{{ $cajaActual->nombre_completo }}</h5>
                            <small class="text-muted">Caja #{{ $cajaActual->caja_id }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="summary-icon bg-primary-subtle text-primary">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Fecha apertura</p>
                            <h5 class="mb-0 fw-semibold">
                                {{ date('d/m/Y', strtotime($cajaActual->fecha_apertura)) }}
                            </h5>
                            <small class="text-muted">{{ date('H:i', strtotime($cajaActual->fecha_apertura)) }} hs</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="summary-icon bg-warning-subtle text-warning">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Monto inicial</p>
                            <h5 class="mb-0 fw-bold">Gs. {{ number_format($cajaActual->monto_inicial, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($resumenCaja)
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted mb-1 small">Ingresos en efectivo</p>
                            <h5 class="mb-0 fw-bold text-success">
                                Gs. {{ number_format($resumenCaja->ingresos_efectivo, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted mb-1 small">Ingresos por transferencia</p>
                            <h5 class="mb-0 fw-bold text-info">
                                Gs. {{ number_format($resumenCaja->ingresos_transferencia, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted mb-1 small">Egresos</p>
                            <h5 class="mb-0 fw-bold text-danger">
                                Gs. {{ number_format($resumenCaja->egresos, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 border-3 border-success">
                        <div class="card-body py-3">
                            <p class="text-muted mb-1 small">Efectivo esperado</p>
                            <h5 class="mb-0 fw-bold text-success">
                                Gs. {{ number_format($resumenCaja->monto_esperado, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ventas') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart me-1"></i> Nueva Venta
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCerrarCaja">
                <i class="fas fa-lock me-1"></i> Cerrar Caja
            </button>
        </div>

    @endif

</div>

@if($cajaActual)

    <div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-labelledby="modalCerrarCajaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('caja.cerrar') }}" id="frmCerrarCaja" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCerrarCajaLabel">
                        <i class="fas fa-lock me-2"></i>Cerrar Caja
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="caja_id" value="{{ $cajaActual->caja_id }}">

                    <div class="alert alert-info d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Caja #{{ $cajaActual->caja_id }}</strong><br>
                            Efectivo esperado:
                            <strong>Gs. {{ number_format($resumenCaja->monto_esperado ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Efectivo contado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-muted"></i></span>
                            <input type="text" id="monto_cierre" name="monto_cierre" class="form-control" autocomplete="off" required>
                        </div>
                        <small class="text-muted">Ingrese el dinero físico contado al cerrar la caja</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <textarea name="observacion" class="form-control" rows="3" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-lock me-1"></i> Confirmar cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

@endif

<style>
.state-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
</style>
@endsection

@section('scripts')
<script>
    const montoInicial = document.getElementById('monto_inicial');
    if (montoInicial) {
        new AutoNumeric('#monto_inicial', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            currencySymbol: 'Gs. ',
            currencySymbolPlacement: 'p',
            minimumValue: '0'
        });
    }

    const montoCierre = document.getElementById('monto_cierre');
    if (montoCierre) {
        new AutoNumeric('#monto_cierre', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            currencySymbol: 'Gs. ',
            currencySymbolPlacement: 'p',
            minimumValue: '0'
        });
    }

    const frmAbrirCaja = document.getElementById('frmAbrirCaja');
    if (frmAbrirCaja) {
        frmAbrirCaja.addEventListener('submit', function() {
            const el = AutoNumeric.getAutoNumericElement('#monto_inicial');
            if (el) document.getElementById('monto_inicial').value = el.getNumericString();
        });
    }

    const frmCerrarCaja = document.getElementById('frmCerrarCaja');
    if (frmCerrarCaja) {
        frmCerrarCaja.addEventListener('submit', function() {
            const el = AutoNumeric.getAutoNumericElement('#monto_cierre');
            if (el) document.getElementById('monto_cierre').value = el.getNumericString();
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('#mensajeSuccess, #mensajeError')
            .forEach(function(alerta) {
                setTimeout(function() {
                    bootstrap.Alert.getOrCreateInstance(alerta).close();
                }, 3000);
            });
    });
</script>
@endsection
