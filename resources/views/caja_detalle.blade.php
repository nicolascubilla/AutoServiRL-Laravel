@extends('layouts.app')

@section('title', 'Detalle de Caja | Autoservice R & L')

@section('content')
<div class="container-fluid">

    @if(!$detalleCaja)

        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <p class="text-muted">La caja solicitada no existe o no se encuentra cerrada.</p>
            <a href="{{ route('caja.historial') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al historial
            </a>
        </div>

    @else

        @php
            $caja = $detalleCaja['caja'];
            $resumen = $detalleCaja['resumen'];
        @endphp

        <div class="page-header">
            <div>
                <h2 class="mb-0"><i class="fas fa-cash-register me-2"></i>Detalle de Caja #{{ $caja->caja_id }}</h2>
                <p class="text-muted mb-0">Resumen completo de la jornada</p>
            </div>
            <a href="{{ route('caja.historial') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al historial
            </a>
        </div>

        <div class="row g-3 mb-4">

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success-subtle border-0">
                        <strong class="text-success"><i class="fas fa-lock-open me-2"></i>Apertura</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kv-icon bg-success-subtle text-success">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Usuario</small>
                                <strong class="fs-6">{{ $caja->usuario_apertura }}</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kv-icon bg-primary-subtle text-primary">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Fecha</small>
                                <strong>{{ date('d/m/Y H:i', strtotime($caja->fecha_apertura)) }}</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="kv-icon bg-warning-subtle text-warning">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Monto inicial</small>
                                <strong class="fs-5">Gs. {{ number_format($caja->monto_inicial, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger-subtle border-0">
                        <strong class="text-danger"><i class="fas fa-lock me-2"></i>Cierre</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kv-icon bg-secondary-subtle text-secondary">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Usuario</small>
                                <strong>{{ $caja->usuario_cierre ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kv-icon bg-primary-subtle text-primary">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Fecha</small>
                                <strong>{{ date('d/m/Y H:i', strtotime($caja->fecha_cierre)) }}</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="kv-icon bg-danger-subtle text-danger">
                                <i class="fas fa-money-bill-1-wave"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Efectivo contado</small>
                                <strong class="fs-5">Gs. {{ number_format($caja->monto_cierre, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Efectivo inicial</p>
                        <h6 class="mb-0 fw-bold">Gs. {{ number_format($resumen['monto_inicial'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Ingresos en efectivo</p>
                        <h6 class="mb-0 fw-bold text-success">Gs. {{ number_format($resumen['ingresos_efectivo'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Ingresos por transferencia</p>
                        <h6 class="mb-0 fw-bold text-info">Gs. {{ number_format($resumen['ingresos_transferencia'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Egresos</p>
                        <h6 class="mb-0 fw-bold text-danger">Gs. {{ number_format($resumen['egresos'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>

        @php
            $diferencia = $resumen['diferencia'];
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-3 border-primary">
                    <div class="card-body text-center">
                        <small class="text-muted d-block mb-1">Efectivo esperado</small>
                        <h5 class="mb-0 fw-bold text-primary">Gs. {{ number_format($resumen['monto_esperado'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <small class="text-muted d-block mb-1">Efectivo contado</small>
                        <h5 class="mb-0 fw-bold">Gs. {{ number_format($resumen['monto_cierre'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-3 {{ $diferencia == 0
                        ? 'border-success'
                        : ($diferencia < 0
                            ? 'border-danger'
                            : 'border-warning') }}">
                    <div class="card-body text-center">
                        <small class="text-muted d-block mb-1">Diferencia</small>
                        <h5 class="mb-0 fw-bold {{ $diferencia == 0
                                ? 'text-success'
                                : ($diferencia < 0
                                    ? 'text-danger'
                                    : 'text-warning') }}">
                            Gs. {{ number_format($diferencia, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-0">
                <strong><i class="fas fa-comment me-2"></i>Observación</strong>
            </div>
            <div class="card-body">
                @if(!empty($caja->observacion))
                    {!! nl2br(e($caja->observacion)) !!}
                @else
                    <span class="text-muted">Sin observación.</span>
                @endif
            </div>
        </div>

    @endif

</div>

<style>
.kv-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
</style>
@endsection
