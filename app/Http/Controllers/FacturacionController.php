<?php

namespace App\Http\Controllers;

use App\Models\FacturacionConfig;
use App\Services\ClienteRuc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturacionController extends Controller
{
    public function index()
    {
        $configuracion = FacturacionConfig::orderBy('config_id')->first();

        return view('facturacion_config', ['configuracion' => $configuracion]);
    }

    public function actualizar(Request $request)
    {
        $config_id = (int) $request->input('config_id', 0);

        if ($config_id <= 0) {
            return redirect()->route('facturacion.config')
                ->with('error', 'Configuración no válida.');
        }

        $afectadas = DB::table('facturacion_config')
            ->where('config_id', $config_id)
            ->update([
                'nombre_negocio' => trim((string) $request->input('nombre_negocio', '')),
                'actividad' => trim((string) $request->input('actividad', '')),
                'propietario' => trim((string) $request->input('propietario', '')),
                'ruc' => trim((string) $request->input('ruc', '')),
                'direccion' => trim((string) $request->input('direccion', '')),
                'telefono' => trim((string) $request->input('telefono', '')),
                'ciudad' => trim((string) $request->input('ciudad', '')),
                'timbrado' => trim((string) $request->input('timbrado', '')),
                'fecha_inicio_vigencia' => $request->input('fecha_inicio_vigencia'),
                'fecha_fin_vigencia' => $request->input('fecha_fin_vigencia'),
                'establecimiento' => trim((string) $request->input('establecimiento', '')),
                'punto_expedicion' => trim((string) $request->input('punto_expedicion', '')),
                'ultimo_numero_factura' => (int) $request->input('ultimo_numero_factura', 0),
            ]);

        if ($afectadas >= 0) {
            return redirect()->route('facturacion.config')
                ->with('success', 'Configuración de facturación actualizada correctamente.');
        }

        return redirect()->route('facturacion.config')
            ->with('error', 'No fue posible actualizar la configuración.');
    }

    public function buscarRuc(Request $request)
    {
        $ruc = trim((string) $request->input('ruc', ''));

        if ($ruc === '') {
            return response()->json(['success' => false, 'mensaje' => 'Debe ingresar un RUC.']);
        }

        $resultado = (new ClienteRuc())->buscarPorRuc($ruc);

        return response()->json($resultado);
    }

    public function facturarVenta($venta)
    {
        $venta_id = (int) $venta;

        if ($venta_id <= 0) {
            return redirect()->route('ventas')
                ->with('error', 'Venta inválida.');
        }

        $venta = DB::table('ventas')->where('venta_id', $venta_id)->first();

        if (!$venta) {
            return redirect()->route('ventas')
                ->with('error', 'No se encontró la venta solicitada.');
        }

        $detalle = $this->obtenerDetalleVenta($venta_id);

        if (count($detalle) === 0) {
            return redirect()->route('ventas')
                ->with('error', 'La venta no tiene productos registrados.');
        }

        $calculos = $this->calcularTotalesFactura($detalle);
        $configuracion = FacturacionConfig::orderBy('config_id')->first();

        $totales = [
            'total_exenta' => $calculos['total_exenta'],
            'total_gravada_5' => $calculos['total_gravada_5'],
            'total_gravada_10' => $calculos['total_gravada_10'],
            'total_iva_5' => $calculos['total_iva_5'],
            'total_iva_10' => $calculos['total_iva_10'],
            'total_iva' => $calculos['total_iva'],
            'total' => $calculos['total'],
        ];

        return view('facturacion', compact('venta', 'detalle', 'configuracion', 'totales'));
    }

    public function emitirFactura(Request $request)
    {
        try {
            $input = $request->json()->all();

            $venta_id = (int) ($input['venta_id'] ?? 0);
            $cliente_documento = trim((string) ($input['cliente_documento'] ?? ''));
            $cliente_nombre = trim((string) ($input['cliente_nombre'] ?? ''));

            if ($venta_id <= 0) {
                throw new \Exception('La venta indicada no es válida.');
            }
            if ($cliente_documento === '') {
                throw new \Exception('Debe ingresar el RUC o documento del cliente.');
            }
            if ($cliente_nombre === '') {
                throw new \Exception('Debe ingresar el nombre o razón social.');
            }

            $configuracion = FacturacionConfig::orderBy('config_id')->first();

            if (!$configuracion) {
                throw new \Exception('No existe una configuración de facturación.');
            }

            $resultado = $this->guardarFactura([
                'venta_id' => $venta_id,
                'cliente_documento' => $cliente_documento,
                'cliente_nombre' => $cliente_nombre,
            ], $configuracion);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 400);
        }
    }

    public function verFactura($factura)
    {
        $factura_id = (int) $factura;

        if ($factura_id <= 0) {
            return redirect()->route('ventas')->with('error', 'Factura inválida.');
        }

        $factura = DB::table('facturas')->where('factura_id', $factura_id)->first();

        if (!$factura) {
            return redirect()->route('ventas')->with('error', 'No se encontró la factura solicitada.');
        }

        $detalle = DB::table('factura_detalle')
            ->where('factura_id', $factura_id)
            ->orderBy('factura_detalle_id')
            ->get();

        $configuracion = FacturacionConfig::orderBy('config_id')->first();

        return view('factura_ver', ['factura' => $factura, 'detalle' => $detalle, 'configuracion' => $configuracion]);
    }

    public function imprimirFactura($factura)
    {
        $factura_id = (int) $factura;

        if ($factura_id <= 0) {
            return redirect()->route('ventas')->with('error', 'Factura inválida.');
        }

        $factura = DB::table('facturas')->where('factura_id', $factura_id)->first();

        if (!$factura) {
            return redirect()->route('ventas')->with('error', 'No se encontró la factura solicitada.');
        }

        $detalle = DB::table('factura_detalle')
            ->where('factura_id', $factura_id)
            ->orderBy('factura_detalle_id')
            ->get();

        $configuracion = FacturacionConfig::orderBy('config_id')->first();

        if (!$configuracion) {
            return redirect()->route('ventas')->with('error', 'No existe una configuración de facturación.');
        }

        return view('factura_imprimir', ['factura' => $factura, 'detalle' => $detalle, 'configuracion' => $configuracion]);
    }

    public function listarFacturas(Request $request)
    {
        $facturas = DB::table('facturas as f')
            ->select(
                'f.factura_id',
                'f.venta_id',
                'f.numero_factura',
                'f.establecimiento',
                'f.punto_expedicion',
                'f.timbrado',
                'f.cliente_nombre',
                'f.cliente_documento',
                'f.condicion_venta',
                'f.total',
                'f.total_exenta',
                'f.total_iva',
                'f.fecha_emision',
                'f.estado'
            )
            ->orderByDesc('f.fecha_emision')
            ->orderByDesc('f.factura_id')
            ->get();

        return view('factura_historial', ['facturas' => $facturas]);
    }

    public function cotizacion()
    {
        $configuracion = FacturacionConfig::orderBy('config_id')->first();

        return view('cotizacion', ['configuracion' => $configuracion]);
    }

    private function obtenerDetalleVenta(int $venta_id)
    {
        return DB::table('venta_detalle as vd')
            ->select(
                'vd.venta_id',
                'vd.pro_cod',
                'vd.cantidad',
                'vd.precio_unitario',
                'vd.subtotal',
                'p.codigo',
                'p.codigo_barra',
                'p.descripcion',
                'p.tasa_iva'
            )
            ->join('productos as p', 'p.pro_cod', '=', 'vd.pro_cod')
            ->where('vd.venta_id', $venta_id)
            ->orderBy('vd.pro_cod')
            ->get();
    }

    private function calcularTotalesFactura($detalle): array
    {
        $totalExenta = 0;
        $totalGravada5 = 0;
        $totalGravada10 = 0;
        $totalIva5 = 0;
        $totalIva10 = 0;

        foreach ($detalle as $item) {
            $subtotal = (int) $item->subtotal;
            $tasaIva = $item->tasa_iva;

            if ($tasaIva === 'E') {
                $totalExenta += $subtotal;
            } elseif ($tasaIva === '5') {
                $totalGravada5 += $subtotal;
                $totalIva5 += (int) round($subtotal / 21);
            } elseif ($tasaIva === '10') {
                $totalGravada10 += $subtotal;
                $totalIva10 += (int) round($subtotal / 11);
            }
        }

        $total = $totalExenta + $totalGravada5 + $totalGravada10;
        $totalIva = $totalIva5 + $totalIva10;

        return [
            'total_exenta' => $totalExenta,
            'total_gravada_5' => $totalGravada5,
            'total_gravada_10' => $totalGravada10,
            'total_iva_5' => $totalIva5,
            'total_iva_10' => $totalIva10,
            'total_iva' => $totalIva,
            'total' => $total,
        ];
    }

    private function guardarFactura(array $datos, $configuracion): array
    {
        return DB::transaction(function () use ($datos, $configuracion) {
            $venta_id = (int) $datos['venta_id'];
            $cliente_documento = trim($datos['cliente_documento']);
            $cliente_nombre = trim($datos['cliente_nombre']);

            $venta = DB::table('ventas')->where('venta_id', $venta_id)->first();

            if (!$venta) {
                throw new \Exception('No se encontró la venta.');
            }

            $facturaExistente = DB::table('facturas')
                ->where('venta_id', $venta_id)
                ->where('estado', 'A')
                ->first();

            if ($facturaExistente) {
                throw new \Exception('Esta venta ya fue facturada.');
            }

            $detalle = $this->obtenerDetalleVenta($venta_id);

            if (count($detalle) === 0) {
                throw new \Exception('La venta no tiene productos.');
            }

            $total_exenta = 0;
            $total_iva_5 = 0;
            $total_iva_10 = 0;
            $total_iva = 0;
            $total_factura = 0;
            $detalleFactura = [];

            foreach ($detalle as $item) {
                $subtotal = (int) $item->subtotal;
                $tasa_iva = $item->tasa_iva;

                $exenta = 0;
                $gravada_5 = 0;
                $gravada_10 = 0;

                if ($tasa_iva === 'E') {
                    $exenta = $subtotal;
                    $total_exenta += $subtotal;
                } elseif ($tasa_iva === '5') {
                    $gravada_5 = $subtotal;
                    $total_iva_5 += round($subtotal / 21);
                } elseif ($tasa_iva === '10') {
                    $gravada_10 = $subtotal;
                    $total_iva_10 += round($subtotal / 11);
                } else {
                    throw new \Exception('La tasa de IVA del producto no es válida.');
                }

                $total_factura += $subtotal;

                $detalleFactura[] = [
                    'pro_cod' => $item->pro_cod,
                    'descripcion' => $item->descripcion,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'exenta' => $exenta,
                    'gravada_5' => $gravada_5,
                    'gravada_10' => $gravada_10,
                    'subtotal' => $subtotal,
                ];
            }

            $total_iva = $total_iva_5 + $total_iva_10;

            if ($total_factura != (int) $venta->total) {
                throw new \Exception('El total de la factura no coincide con el total de la venta.');
            }

            $numeroFactura = (int) $configuracion->ultimo_numero_factura + 1;

            $factura_id = DB::table('facturas')->insertGetId([
                'venta_id' => $venta_id,
                'numero_factura' => $numeroFactura,
                'establecimiento' => $configuracion->establecimiento,
                'punto_expedicion' => $configuracion->punto_expedicion,
                'timbrado' => $configuracion->timbrado,
                'cliente_nombre' => $cliente_nombre,
                'cliente_documento' => $cliente_documento,
                'condicion_venta' => 'C',
                'total' => $total_factura,
                'total_exenta' => $total_exenta,
                'total_iva_5' => $total_iva_5,
                'total_iva_10' => $total_iva_10,
                'total_iva' => $total_iva,
            ], 'factura_id');

            foreach ($detalleFactura as $item) {
                DB::table('factura_detalle')->insert([
                    'factura_id' => $factura_id,
                    'pro_cod' => $item['pro_cod'],
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'exenta' => $item['exenta'],
                    'gravada_5' => $item['gravada_5'],
                    'gravada_10' => $item['gravada_10'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::table('facturacion_config')
                ->where('config_id', $configuracion->config_id)
                ->update(['ultimo_numero_factura' => $numeroFactura]);

            return [
                'success' => true,
                'factura_id' => (int) $factura_id,
                'numero_factura' => $numeroFactura,
                'total' => $total_factura,
            ];
        });
    }
}
