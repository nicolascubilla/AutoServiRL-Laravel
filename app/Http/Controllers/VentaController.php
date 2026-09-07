<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $usuario_id = (int) session('user_id');

        $caja = DB::table('cajas')
            ->select('caja_id', 'usuario_apertura_id', 'fecha_apertura', 'monto_inicial', 'estado')
            ->where('usuario_apertura_id', $usuario_id)
            ->where('estado', 'A')
            ->orderByDesc('caja_id')
            ->first();

        return view('ventas', ['caja' => $caja]);
    }

    public function buscarPorCodigoBarra(Request $request)
    {
        $codigo_barra = trim((string) $request->input('codigo_barra', ''));

        if ($codigo_barra === '') {
            return response()->json([
                'success' => false,
                'mensaje' => 'Código de barras vacío.',
            ]);
        }

        $producto = DB::table('productos')
            ->select('pro_cod', 'codigo', 'codigo_barra', 'descripcion', 'precio')
            ->where('codigo_barra', $codigo_barra)
            ->where('activo', 'S')
            ->first();

        if (!$producto) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Producto no encontrado.',
            ]);
        }

        return response()->json([
            'success' => true,
            'producto' => $producto,
        ]);
    }

    public function guardar(Request $request)
    {
        $usuario_id = (int) session('user_id');
        $datos = $request->json()->all();

        if (!is_array($datos)) {
            return response()->json(['success' => false, 'mensaje' => 'Datos de venta inválidos.']);
        }

        $carrito = $datos['carrito'] ?? [];
        if (!is_array($carrito) || count($carrito) === 0) {
            return response()->json(['success' => false, 'mensaje' => 'La venta no contiene productos.']);
        }

        $forma_pago = $datos['forma_pago'] ?? '';
        if (!in_array($forma_pago, ['E', 'T'], true)) {
            return response()->json(['success' => false, 'mensaje' => 'Forma de pago inválida.']);
        }

        $monto_recibido = (int) ($datos['monto_recibido'] ?? 0);

        try {
            $resultado = $this->guardarVenta($usuario_id, $carrito, $forma_pago, $monto_recibido);

            return response()->json([
                'success' => true,
                'mensaje' => 'Venta registrada correctamente.',
                'venta_id' => $resultado['venta_id'],
                'total' => $resultado['total'],
                'vuelto' => $resultado['vuelto'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 400);
        }
    }

    public function listar()
    {
        $historialVentas = DB::table('ventas as v')
            ->select(
                'v.venta_id',
                'v.fecha_venta',
                'v.total',
                'v.estado',
                'v.caja_id',
                'u.nombre_completo AS cajero',
                'vp.forma_pago',
                'vp.monto AS monto_pagado',
                'f.factura_id AS factura_id',
                'f.numero_factura',
                'f.establecimiento',
                'f.punto_expedicion'
            )
            ->join('usuarios as u', 'u.id_usuario', '=', 'v.usuario_id')
            ->leftJoin('venta_pagos as vp', 'vp.venta_id', '=', 'v.venta_id')
            ->leftJoin('facturas as f', function ($join) {
                $join->on('f.venta_id', '=', 'v.venta_id')
                    ->where('f.estado', '=', 'A');
            })
            ->orderByDesc('v.fecha_venta')
            ->orderByDesc('v.venta_id')
            ->get();

        return view('ventas_historial', ['historialVentas' => $historialVentas]);
    }

    public function anular(Request $request)
    {
        $venta_id = (int) $request->input('venta_id', 0);
        $motivo = trim((string) $request->input('motivo', ''));
        $usuario_id = (int) session('user_id');

        try {
            $this->anularVenta($venta_id, $usuario_id, $motivo !== '' ? $motivo : null);
            session()->flash('success', 'Venta anulada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('ventas.historial');
    }

    private function guardarVenta(int $usuario_id, array $carrito, string $forma_pago, int $monto_recibido): array
    {
        return DB::transaction(function () use ($usuario_id, $carrito, $forma_pago, $monto_recibido) {
            // 1. Obtener caja abierta (bloqueada)
            $caja_id = DB::table('cajas')
                ->where('caja_id', function ($q) use ($usuario_id) {
                    $q->select('caja_id')
                        ->from('cajas')
                        ->where('usuario_apertura_id', $usuario_id)
                        ->where('estado', 'A')
                        ->orderByDesc('caja_id')
                        ->limit(1);
                })
                ->lockForUpdate()
                ->value('caja_id');

            if (!$caja_id) {
                throw new \Exception('No existe una caja abierta.');
            }
            $caja_id = (int) $caja_id;

            // 2. Insertar cabecera de venta
            $venta_id = DB::table('ventas')->insertGetId([
                'caja_id' => $caja_id,
                'usuario_id' => $usuario_id,
                'total' => 0,
                'estado' => 'P',
            ], 'venta_id');

            $total = 0;

            // 4. Procesar cada producto
            foreach ($carrito as $item) {
                $pro_cod = (int) ($item['pro_cod'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);

                if ($pro_cod <= 0 || $cantidad <= 0) {
                    throw new \Exception('Producto o cantidad inválida.');
                }

                // Buscar producto y bloquear stock
                $producto = DB::table('productos as p')
                    ->select('p.pro_cod', 'p.descripcion', 'p.precio', 's.cantidad AS stock')
                    ->join('stock as s', 's.pro_cod', '=', 'p.pro_cod')
                    ->where('p.pro_cod', $pro_cod)
                    ->where('p.activo', 'S')
                    ->lockForUpdate()
                    ->first();

                if (!$producto) {
                    throw new \Exception('Uno de los productos ya no está disponible.');
                }

                $stock = $producto->stock !== null ? (float) $producto->stock : 0;
                if ($stock < $cantidad) {
                    throw new \Exception('Stock insuficiente para: ' . $producto->descripcion . '. Disponible: ' . $stock);
                }

                $precio = (int) $producto->precio;
                $subtotal = (int) round($cantidad * $precio);
                $total += $subtotal;

                // Detalle
                DB::table('venta_detalle')->insert([
                    'venta_id' => $venta_id,
                    'pro_cod' => $pro_cod,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);

                // Actualizar stock
                $stockResultante = DB::table('stock')
                    ->where('pro_cod', $pro_cod)
                    ->where('cantidad', '>=', $cantidad)
                    ->update([
                        'cantidad' => DB::raw('cantidad - ' . $cantidad),
                        'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP'),
                    ]);

                if (!$stockResultante) {
                    throw new \Exception('No fue posible actualizar el stock de: ' . $producto->descripcion);
                }

                $nuevoStock = DB::table('stock')->where('pro_cod', $pro_cod)->value('cantidad');

                // Historial de movimiento
                DB::table('stock_movimientos')->insert([
                    'pro_cod' => $pro_cod,
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'stock_resultante' => (float) $nuevoStock,
                    'observacion' => 'Venta',
                    'usuario_id' => $usuario_id,
                ]);
            }

            if ($total <= 0) {
                throw new \Exception('El total de la venta debe ser mayor a cero.');
            }

            // 6. Actualizar total de venta
            DB::table('ventas')->where('venta_id', $venta_id)->update([
                'total' => $total,
                'monto_recibido' => $forma_pago === 'E' ? $monto_recibido : null,
            ]);

            // 7. Validar pago
            if ($forma_pago === 'E') {
                if ($monto_recibido < $total) {
                    throw new \Exception('El monto recibido es menor al total.');
                }
                $monto_pago = $total;
                $vuelto = $monto_recibido - $total;
            } else {
                $monto_pago = $total;
                $vuelto = 0;
            }

            // 8. Insertar pago
            DB::table('venta_pagos')->insert([
                'venta_id' => $venta_id,
                'forma_pago' => $forma_pago,
                'monto' => $monto_pago,
            ]);

            // 9. Movimiento de caja
            $descripcionMovimiento = $forma_pago === 'E' ? 'Venta en efectivo' : 'Venta por transferencia';
            DB::table('movimientos_caja')->insert([
                'caja_id' => $caja_id,
                'usuario_id' => $usuario_id,
                'venta_id' => $venta_id,
                'tipo' => 'I',
                'forma_pago' => $forma_pago,
                'monto' => $monto_pago,
                'descripcion' => $descripcionMovimiento,
            ]);

            return [
                'venta_id' => (int) $venta_id,
                'total' => $total,
                'vuelto' => $vuelto,
            ];
        });
    }

    private function anularVenta(int $venta_id, int $usuario_id, ?string $motivo): void
    {
        DB::transaction(function () use ($venta_id, $usuario_id, $motivo) {
            // 1. Obtener venta
            $venta = DB::table('ventas')
                ->select('venta_id', 'caja_id', 'total', 'estado')
                ->where('venta_id', $venta_id)
                ->lockForUpdate()
                ->first();

            if (!$venta) {
                throw new \Exception('La venta no existe.');
            }
            if ($venta->estado === 'A') {
                throw new \Exception('La venta ya fue anulada.');
            }

            // 2. Anular facturas de la venta
            DB::table('facturas')
                ->where('venta_id', $venta_id)
                ->where('estado', 'A')
                ->update(['estado' => 'N']);

            // 3. Obtener detalle
            $detalle = DB::table('venta_detalle')
                ->select('pro_cod', 'cantidad')
                ->where('venta_id', $venta_id)
                ->get();

            // 4. Reintegrar stock + historial
            foreach ($detalle as $item) {
                DB::table('stock')
                    ->where('pro_cod', $item->pro_cod)
                    ->update([
                        'cantidad' => DB::raw('cantidad + ' . $item->cantidad),
                        'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP'),
                    ]);

                $stockResultante = DB::table('stock')->where('pro_cod', $item->pro_cod)->value('cantidad');

                DB::table('stock_movimientos')->insert([
                    'pro_cod' => $item->pro_cod,
                    'tipo' => 'entrada',
                    'cantidad' => $item->cantidad,
                    'stock_resultante' => $stockResultante,
                    'observacion' => 'Anulación de venta #' . $venta_id,
                    'usuario_id' => $usuario_id,
                ]);
            }

            // 5-6. Movimiento de caja (egreso) para reversar
            $pago = DB::table('venta_pagos')
                ->select('forma_pago', 'monto')
                ->where('venta_id', $venta_id)
                ->limit(1)
                ->first();

            if ($pago && !empty($venta->caja_id)) {
                DB::table('movimientos_caja')->insert([
                    'caja_id' => $venta->caja_id,
                    'usuario_id' => $usuario_id,
                    'venta_id' => $venta_id,
                    'tipo' => 'E',
                    'forma_pago' => $pago->forma_pago,
                    'monto' => $pago->monto,
                    'descripcion' => 'Anulación de venta #' . $venta_id,
                ]);
            }

            // 7. Marcar venta anulada
            $obs = trim((string) $motivo) !== ''
                ? 'Anulada: ' . trim((string) $motivo)
                : 'Venta anulada';

            DB::table('ventas')
                ->where('venta_id', $venta_id)
                ->update([
                    'estado' => 'A',
                    'observacion' => $obs,
                ]);
        });
    }
}
