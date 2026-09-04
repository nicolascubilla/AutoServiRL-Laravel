@extends('layouts.app')

@section('title', 'Ticket de Venta | AutoServiRL')

@section('content')
@php
$esEfectivo = $venta->forma_pago === 'E';
$nombrePago = $esEfectivo ? 'Efectivo' : 'Transferencia';
$vuelto = 0;
if ($esEfectivo) {
    $vuelto = (int)$venta->monto_pagado - (int)$venta->total;
}
@endphp

<div class="container py-4">

    <div class="row justify-content-center">

        <div class="col-md-7 col-lg-5">

            <div class="d-flex justify-content-between mb-3 no-print">

                <a
                    href="{{ route('ventas') }}"
                    class="btn btn-secondary">

                    <i class="fas fa-arrow-left"></i>

                    Nueva Venta

                </a>


                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()">

                    <i class="fas fa-print"></i>

                    Imprimir

                </button>

            </div>


            <div class="card shadow ticket-card">

                <div class="card-body p-4">


                    <div class="text-center mb-4">

                        <h3 class="mb-1 fw-bold">

                            AUTOSERVICE RL

                        </h3>

                        <div class="text-muted small">

                            Comprobante de Venta

                        </div>

                    </div>


                    <hr>


                    <div class="row mb-3">

                        <div class="col-6">

                            <small class="text-muted">

                                N° Venta

                            </small>

                            <div class="fw-bold">

                                #{{ str_pad($venta->venta_id, 6, '0', STR_PAD_LEFT) }}

                            </div>

                        </div>


                        <div class="col-6 text-end">

                            <small class="text-muted">

                                Fecha

                            </small>

                            <div>

                                {{ date('d/m/Y H:i', strtotime($venta->fecha_venta)) }}

                            </div>

                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">

                            Cajero

                        </small>

                        <div>

                            {{ $venta->cajero }}

                        </div>

                    </div>


                    <hr>


                    <h6 class="fw-bold mb-3">

                        Productos

                    </h6>


                    @foreach ($detalle as $item)

                        <div class="mb-3">

                            <div class="fw-semibold">

                                {{ $item->descripcion }}

                            </div>


                            <div class="d-flex justify-content-between small text-muted">

                                <span>

                                    {{ rtrim(rtrim(number_format($item->cantidad, 3, ',', '.'), '0'), ',') }}

                                    x

                                    Gs.

                                    {{ number_format($item->precio_unitario, 0, ',', '.') }}

                                </span>


                                <strong class="text-dark">

                                    Gs.

                                    {{ number_format($item->subtotal, 0, ',', '.') }}

                                </strong>

                            </div>

                        </div>

                    @endforeach


                    <hr>


                    <div
                        class="d-flex justify-content-between align-items-center mb-4">

                        <span class="fs-5 fw-bold">

                            TOTAL

                        </span>

                        <span class="fs-4 fw-bold text-success">

                            Gs.

                            {{ number_format($venta->total, 0, ',', '.') }}

                        </span>

                    </div>


                    <div class="border rounded p-3 mb-4">

                        <div
                            class="d-flex justify-content-between mb-2">

                            <span>

                                Forma de pago

                            </span>

                            <strong>

                                {{ $nombrePago }}

                            </strong>

                        </div>


                        @if ($esEfectivo)

                            <div
                                class="d-flex justify-content-between mb-2">

                                <span>

                                    Efectivo recibido

                                </span>

                                <strong>

                                    Gs.

                                    {{ number_format($venta->monto_pagado, 0, ',', '.') }}

                                </strong>

                            </div>


                            <div
                                class="d-flex justify-content-between">

                                <span class="fw-bold">

                                    Vuelto

                                </span>

                                <strong class="text-success">

                                    Gs.

                                    {{ number_format($vuelto, 0, ',', '.') }}

                                </strong>

                            </div>

                        @endif

                    </div>


                    <div class="text-center text-muted">

                        <p class="mb-1">

                            ¡Gracias por su compra!

                        </p>

                        <small>

                            Vuelva pronto

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

.ticket-card {

    border: none;

    border-radius: 16px;

}

@media print {

    body * {
        visibility: hidden;
    }

    .ticket-card,
    .ticket-card * {
        visibility: visible;
    }

    .ticket-card {

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        box-shadow: none !important;

        border: none;

    }

    .no-print {
        display: none !important;
    }

}

</style>
@endsection
