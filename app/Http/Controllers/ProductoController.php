<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = DB::table('productos as p')
            ->select(
                'p.*',
                DB::raw('COALESCE(s.cantidad, 0) AS cantidad'),
                DB::raw('COALESCE(s.stock_minimo, 0) AS stock_minimo')
            )
            ->leftJoin('stock as s', 's.pro_cod', '=', 'p.pro_cod')
            ->orderByDesc('p.pro_cod')
            ->get();

        return view('productos', ['productos' => $productos]);
    }

    public function guardar(Request $request)
    {
        $tasaIva = $request->input('tasa_iva', '10');

        if (!in_array($tasaIva, ['E', '5', '10'], true)) {
            return redirect()->route('productos')
                ->with('error', 'La tasa de IVA seleccionada no es válida.');
        }

        $pro_cod = (int) $request->input('pro_cod', 0);
        $datos = $request->only(['codigo', 'codigo_barra', 'descripcion', 'precio', 'tasa_iva', 'cantidad', 'stock_minimo']);

        try {
            if ($pro_cod <= 0) {
                $this->insertar($datos);
                session()->flash('success', 'Producto registrado correctamente.');
            } else {
                $this->actualizar((array) $datos + ['pro_cod' => $pro_cod]);
                session()->flash('success', 'Producto actualizado correctamente.');
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('productos');
    }

    public function cambiarEstado(Request $request)
    {
        $pro_cod = (int) $request->input('pro_cod', 0);

        DB::table('productos')
            ->where('pro_cod', $pro_cod)
            ->update([
                'activo' => DB::raw("CASE WHEN activo='S' THEN 'N' ELSE 'S' END"),
            ]);

        return redirect()->route('productos')
            ->with('success', 'Estado del producto actualizado correctamente.');
    }

    private function insertar(array $datos): int
    {
        return DB::transaction(function () use ($datos) {
            $pro_cod = DB::table('productos')->insertGetId([
                'codigo' => $datos['codigo'] ?? null,
                'codigo_barra' => $datos['codigo_barra'],
                'descripcion' => $datos['descripcion'],
                'precio' => (int) $datos['precio'],
                'tasa_iva' => $datos['tasa_iva'] ?? '10',
                'activo' => 'S',
            ], 'pro_cod');

            $this->upsertStock($pro_cod, (float) ($datos['cantidad'] ?? 0), (float) ($datos['stock_minimo'] ?? 0));

            return (int) $pro_cod;
        });
    }

    private function actualizar(array $datos): void
    {
        DB::transaction(function () use ($datos) {
            DB::table('productos')
                ->where('pro_cod', (int) $datos['pro_cod'])
                ->update([
                    'codigo' => $datos['codigo'] ?? null,
                    'codigo_barra' => $datos['codigo_barra'],
                    'descripcion' => $datos['descripcion'],
                    'precio' => (int) $datos['precio'],
                    'tasa_iva' => $datos['tasa_iva'] ?? '10',
                ]);

            $this->upsertStock((int) $datos['pro_cod'], null, (float) ($datos['stock_minimo'] ?? 0));
        });
    }

    private function upsertStock(int $pro_cod, ?float $cantidad, float $stock_minimo): void
    {
        $exists = DB::table('stock')->where('pro_cod', $pro_cod)->exists();

        if ($exists) {
            $update = ['stock_minimo' => $stock_minimo, 'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP')];
            if ($cantidad !== null) {
                $update['cantidad'] = $cantidad;
            }
            DB::table('stock')->where('pro_cod', $pro_cod)->update($update);
        } else {
            DB::table('stock')->insert([
                'pro_cod' => $pro_cod,
                'cantidad' => $cantidad ?? 0,
                'stock_minimo' => $stock_minimo,
            ]);
        }
    }
}
