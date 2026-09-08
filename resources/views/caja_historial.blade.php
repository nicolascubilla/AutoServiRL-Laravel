@extends('layouts.app')

@section('title', 'Historial de Cajas | Autoservice R &amp; L')

@section('content')
<div class="container-fluid">

    <div class="page-header">
        <div>
            <h2 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Cajas</h2>
            <p class="text-muted mb-0">Consulte todas las cajas cerradas anteriormente</p>
        </div>
        <a href="{{ route('caja') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Caja
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            @if($historialCajas->isEmpty())

                <div class="text-center py-5">
                    <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Todavía no existen cajas cerradas.</p>
                </div>

            @else

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Caja</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th>Usuario Apertura</th>
                                <th>Usuario Cierre</th>
                                <th class="text-end">Monto Inicial</th>
                                <th class="text-end">Monto Cierre</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historialCajas as $caja)
                                <tr>
                                    <td><strong>#{{ $caja->caja_id }}</strong></td>
                                    <td>{{ date('d/m/Y H:i', strtotime($caja->fecha_apertura)) }}</td>
                                    <td>
                                        @if($caja->fecha_cierre)
                                            {{ date('d/m/Y H:i', strtotime($caja->fecha_cierre)) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $caja->usuario_apertura }}</td>
                                    <td>
                                        @if($caja->usuario_cierre)
                                            {{ $caja->usuario_cierre }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">Gs. {{ number_format($caja->monto_inicial, 0, ',', '.') }}</td>
                                    <td class="text-end"><strong>Gs. {{ number_format($caja->monto_cierre, 0, ',', '.') }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary">
                                            <i class="fas fa-circle me-1"></i> CERRADA
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('caja.detalle', ['caja' => $caja->caja_id]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

        </div>
    </div>

</div>
@endsection
