<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario_id = session('user_id');

        return view('dashboard', [
            'ventas_hoy' => $this->ventasDelDia(),
            'cantidad_ventas' => $this->cantidadVentas(),
            'estado_caja' => $this->estadoCaja($usuario_id),
            'productos_total' => $this->totalProductos(),
            'mejor_venta' => $this->mejorVenta(),
        ]);
    }

    private function ventasDelDia(): int
    {
        return (int) DB::table('ventas')
            ->whereDate('fecha_venta', DB::raw('CURRENT_DATE'))
            ->where('estado', 'P')
            ->sum('total');
    }

    private function cantidadVentas(): int
    {
        return (int) DB::table('ventas')
            ->whereDate('fecha_venta', DB::raw('CURRENT_DATE'))
            ->where('estado', 'P')
            ->count();
    }

    private function estadoCaja($usuario_id): ?object
    {
        return DB::table('cajas')
            ->where('usuario_apertura_id', $usuario_id)
            ->where('estado', 'A')
            ->orderByDesc('caja_id')
            ->first();
    }

    private function totalProductos(): int
    {
        return (int) DB::table('productos')
            ->where('activo', 'S')
            ->count();
    }

    private function mejorVenta(): ?object
    {
        return DB::table('ventas')
            ->select('total', 'fecha_venta')
            ->whereDate('fecha_venta', DB::raw('CURRENT_DATE'))
            ->where('estado', 'P')
            ->orderByDesc('total')
            ->first();
    }
}
