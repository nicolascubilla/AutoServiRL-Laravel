@extends('layouts.app')

@section('title', 'Dashboard | Autoservice R &amp; L')

@section('content')
<?php
$ventas_hoy = $ventas_hoy ?? 0;
$cantidad_ventas = $cantidad_ventas ?? 0;
$estado_caja = $estado_caja ?? false;
$productos_total = $productos_total ?? 0;
$mejor_venta = $mejor_venta ?? false;
$caja_abierta = $estado_caja !== false && $estado_caja !== null;
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha = date('d') . ' de ' . $meses[(int) date('n')] . ' de ' . date('Y');
?>
<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2>Dashboard</h2>
            <p class="text-muted">
                Bienvenido {{ session('nombre') }}
                &mdash;
                {{ $fecha }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ventas') }}" class="btn btn-success">
                <i class="fas fa-cash-register"></i> Nueva Venta
            </a>
            <a href="{{ route('productos') }}" class="btn btn-primary">
                <i class="fas fa-box"></i> Productos
            </a>
            <a href="{{ route('caja') }}" class="btn btn-warning text-dark">
                <i class="fas fa-money-bill-wave"></i> Caja
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="summary-icon bg-success-subtle text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Ventas del Día</p>
                        <h3 class="mb-0 fw-bold">₲ {{ number_format($ventas_hoy, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="summary-icon bg-primary-subtle text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Cantidad Ventas</p>
                        <h3 class="mb-0 fw-bold">{{ $cantidad_ventas }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="summary-icon {{ $caja_abierta ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger' }}">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Estado Caja</p>
                        <h3 class="mb-0 fw-bold {{ $caja_abierta ? 'text-success' : 'text-danger' }}">
                            {{ $caja_abierta ? 'ABIERTA' : 'CERRADA' }}
                        </h3>
                        @if ($caja_abierta)
                            <small class="text-muted">Inicial: ₲ {{ number_format($estado_caja->monto_inicial, 0, ',', '.') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="summary-icon bg-info-subtle text-info">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Productos Registrados</p>
                        <h3 class="mb-0 fw-bold">{{ $productos_total }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-chart-line me-2"></i>
                        Resumen del Día
                    </h5>
                    @if ($cantidad_ventas > 0)
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="text-muted mb-1">Ticket Promedio</h6>
                                    <h4 class="fw-bold mb-0">₲ {{ number_format($ventas_hoy / $cantidad_ventas, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="text-muted mb-1">Mayor Venta</h6>
                                    <h4 class="fw-bold mb-0">₲ {{ number_format($mejor_venta ? $mejor_venta->total : 0, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="text-muted mb-1">Total Vendido</h6>
                                    <h4 class="fw-bold mb-0 text-success">₲ {{ number_format($ventas_hoy, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay ventas registradas hoy.</p>
                            <a href="{{ route('ventas') }}" class="btn btn-success btn-sm mt-3">
                                <i class="fas fa-plus"></i> Registrar Primera Venta
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-bolt me-2"></i>
                        Accesos Rápidos
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('ventas') }}" class="btn btn-outline-success text-start py-3">
                            <i class="fas fa-cash-register me-2"></i><strong>Punto de Venta</strong><br>
                            <small class="text-muted">Registrar una nueva venta</small>
                        </a>
                        <a href="{{ route('productos') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="fas fa-box me-2"></i><strong>Gestión de Productos</strong><br>
                            <small class="text-muted">Administrar inventario</small>
                        </a>
                        <a href="{{ route('caja') }}" class="btn btn-outline-warning text-start py-3">
                            <i class="fas fa-money-bill-wave me-2"></i><strong>Control de Caja</strong><br>
                            <small class="text-muted">{{ $caja_abierta ? 'Cerrar caja abierta' : 'Abrir nueva caja' }}</small>
                        </a>
                        <a href="{{ route('caja.historial') }}" class="btn btn-outline-secondary text-start py-3">
                            <i class="fas fa-history me-2"></i><strong>Historial de Cajas</strong><br>
                            <small class="text-muted">Ver movimientos anteriores</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.summary-icon {
    width: 56px; height: 56px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.summary-card { border-radius: 14px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.summary-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,.08) !important; }
.section-title { font-weight: 600; font-size: 1.05rem; color: #495057; }
</style>
@endsection
