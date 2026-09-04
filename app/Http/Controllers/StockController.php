<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $productos = DB::table('productos as p')
            ->select(
                'p.pro_cod',
                'p.codigo',
                'p.codigo_barra',
                'p.descripcion',
                'p.precio',
                'p.activo',
                DB::raw('COALESCE(s.cantidad, 0) AS cantidad'),
                DB::raw('COALESCE(s.stock_minimo, 0) AS stock_minimo'),
                's.fecha_actualizacion'
            )
            ->leftJoin('stock as s', 's.pro_cod', '=', 'p.pro_cod')
            ->orderByDesc('p.pro_cod')
            ->get();

        return view('stock', ['productos' => $productos]);
    }

    public function entrada(Request $request)
    {
        return $this->movimiento('entrada', $request);
    }

    public function salida(Request $request)
    {
        return $this->movimiento('salida', $request);
    }

    private function movimiento(string $tipo, Request $request)
    {
        $pro_cod = (int) $request->input('pro_cod', 0);
        $cantidad = (float) $request->input('cantidad', 0);
        $observacion = trim((string) $request->input('observacion', ''));
        $usuario_id = (int) session('user_id');

        try {
            $this->registrarMovimiento($pro_cod, $tipo, $cantidad, $observacion !== '' ? $observacion : null, $usuario_id);
            session()->flash('success', $tipo === 'entrada'
                ? 'Entrada de stock registrada correctamente.'
                : 'Salida de stock registrada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('stock');
    }

    public function historial(Request $request)
    {
        $pro_cod = (int) $request->input('pro_cod', 0);

        $movimientos = DB::table('stock_movimientos as m')
            ->select(
                'm.mov_id',
                'm.tipo',
                'm.cantidad',
                'm.stock_resultante',
                'm.observacion',
                'm.fecha_movimiento',
                DB::raw("COALESCE(u.nombre_completo, 'Sistema') AS usuario")
            )
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'm.usuario_id')
            ->where('m.pro_cod', $pro_cod)
            ->orderByDesc('m.fecha_movimiento')
            ->orderByDesc('m.mov_id')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'movimientos' => $movimientos]);
    }

    public function minimo(Request $request)
    {
        $pro_cod = (int) $request->input('pro_cod', 0);
        $stock_minimo = (float) str_replace(',', '', $request->input('stock_minimo', '0'));

        try {
            $this->actualizarMinimo($pro_cod, $stock_minimo);
            session()->flash('success', 'Stock mínimo actualizado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('stock');
    }

    private function registrarMovimiento(int $pro_cod, string $tipo, float $cantidad, ?string $observacion, ?int $usuario_id): void
    {
        if ($pro_cod <= 0 || $cantidad <= 0) {
            throw new \Exception('Datos de movimiento inválidos.');
        }
        if (!in_array($tipo, ['entrada', 'salida'], true)) {
            throw new \Exception('Tipo de movimiento inválido.');
        }

        DB::transaction(function () use ($pro_cod, $tipo, $cantidad, $observacion, $usuario_id) {
            $stock_resultante = null;

            if ($tipo === 'salida') {
                $actual = DB::table('stock')->where('pro_cod', $pro_cod)->lockForUpdate()->value('cantidad');

                if ($actual === null) {
                    throw new \Exception('El producto no tiene stock registrado.');
                }
                $actual = (float) $actual;
                if ($actual < $cantidad) {
                    throw new \Exception('Stock insuficiente. Disponible: ' . $actual);
                }

                $stock_resultante = $actual - $cantidad;
                DB::table('stock')
                    ->where('pro_cod', $pro_cod)
                    ->update([
                        'cantidad' => DB::raw('cantidad - ' . $cantidad),
                        'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP'),
                    ]);
            } else {
                DB::table('stock')->upsert(
                    ['pro_cod' => $pro_cod, 'cantidad' => $cantidad, 'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP')],
                    ['pro_cod'],
                    ['cantidad' => DB::raw('stock.cantidad + EXCLUDED.cantidad'), 'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP')]
                );
                $stock_resultante = (float) DB::table('stock')->where('pro_cod', $pro_cod)->value('cantidad');
            }

            DB::table('stock_movimientos')->insert([
                'pro_cod' => $pro_cod,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_resultante' => $stock_resultante,
                'observacion' => $observacion,
                'usuario_id' => $usuario_id,
            ]);
        });
    }

    private function actualizarMinimo(int $pro_cod, float $stock_minimo): void
    {
        if ($pro_cod <= 0 || $stock_minimo < 0) {
            throw new \Exception('Datos de stock mínimo inválidos.');
        }

        $exists = DB::table('stock')->where('pro_cod', $pro_cod)->exists();
        if ($exists) {
            DB::table('stock')->where('pro_cod', $pro_cod)->update(['stock_minimo' => $stock_minimo]);
        } else {
            DB::table('stock')->insert(['pro_cod' => $pro_cod, 'cantidad' => 0, 'stock_minimo' => $stock_minimo]);
        }
    }
}
