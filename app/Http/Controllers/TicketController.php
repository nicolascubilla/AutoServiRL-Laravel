<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function mostrar($venta)
    {
        $venta_id = (int) $venta;

        if ($venta_id <= 0) {
            return redirect()->route('ventas');
        }

        $venta = DB::table('ventas as v')
            ->select(
                'v.venta_id',
                'v.fecha_venta',
                'v.total',
                'v.estado',
                'u.nombre_completo AS cajero',
                'vp.forma_pago',
                'vp.monto AS monto_pagado'
            )
            ->join('usuarios as u', 'u.id_usuario', '=', 'v.usuario_id')
            ->leftJoin('venta_pagos as vp', 'vp.venta_id', '=', 'v.venta_id')
            ->where('v.venta_id', $venta_id)
            ->first();

        if (!$venta) {
            return redirect()->route('ventas')
                ->with('error', 'No se encontró la venta solicitada.');
        }

        $detalle = DB::table('venta_detalle as vd')
            ->select(
                'vd.venta_detalle_id',
                'vd.pro_cod',
                'p.descripcion',
                'vd.cantidad',
                'vd.precio_unitario',
                'vd.subtotal'
            )
            ->join('productos as p', 'p.pro_cod', '=', 'vd.pro_cod')
            ->where('vd.venta_id', $venta_id)
            ->orderBy('vd.venta_detalle_id')
            ->get();

        return view('ticket', ['venta' => $venta, 'detalle' => $detalle]);
    }
}
