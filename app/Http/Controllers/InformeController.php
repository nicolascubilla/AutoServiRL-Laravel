<?php

namespace App\Http\Controllers;

use App\Models\FacturacionConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        $datos = $this->calcular($request);

        return view('informes', $datos);
    }

    public function exportarCsv(Request $request)
    {
        $tipo = $request->input('tipo', 'ventas');

        $fechaDesde = $this->fechaDesde($request);
        $fechaHasta = $this->fechaHasta($request);

        $nombre = 'informe_' . $tipo . '_' . str_replace('-', '', $fechaDesde) . '_' . str_replace('-', '', $fechaHasta) . '.csv';

        return response()->streamDownload(function () use ($tipo, $fechaDesde, $fechaHasta) {
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');

            if ($tipo === 'ventas') {
                $filas = $this->ventasPorDia($fechaDesde, $fechaHasta);
                fputcsv($out, ['Fecha', 'Cantidad de ventas', 'Total (Gs.)'], ';', '"', '\\');

                $total = 0;
                foreach ($filas as $f) {
                    $total += (int) $f->total;
                    fputcsv($out, [
                        date('d/m/Y', strtotime($f->fecha)),
                        (int) $f->cantidad,
                        (int) $f->total,
                    ], ';', '"', '\\');
                }

                fputcsv($out, ['TOTAL', '', $total], ';', '"', '\\');
            } else {
                $filas = $this->topProductos($fechaDesde, $fechaHasta);
                fputcsv($out, ['Producto', 'Cantidad', 'Total (Gs.)'], ';', '"', '\\');

                foreach ($filas as $p) {
                    fputcsv($out, [
                        $p->descripcion,
                        (float) $p->cantidad,
                        (int) $p->total,
                    ], ';', '"', '\\');
                }
            }

            fclose($out);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function imprimir(Request $request)
    {
        $datos = $this->calcular($request);
        $datos['configuracion'] = FacturacionConfig::orderBy('config_id')->first();

        return view('informes_imprimir', $datos);
    }

    private function calcular(Request $request): array
    {
        $fechaDesde = $this->fechaDesde($request);
        $fechaHasta = $this->fechaHasta($request);

        if ($fechaDesde > $fechaHasta) {
            $tmp = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        return [
            'desde' => $fechaDesde,
            'hasta' => $fechaHasta,
            'informeVentas' => $this->resumenVentas($fechaDesde, $fechaHasta),
            'ventasPorDia' => $this->ventasPorDia($fechaDesde, $fechaHasta),
            'informeFacturacion' => $this->resumenFacturacion($fechaDesde, $fechaHasta),
            'topProductos' => $this->topProductos($fechaDesde, $fechaHasta),
        ];
    }

    private function resumenVentas(string $desde, string $hasta): object
    {
        [$inicio, $fin] = $this->rangoFechas($desde, $hasta);

        return DB::table('ventas as v')
            ->selectRaw('
                COUNT(v.venta_id) AS cantidad,
                COALESCE(SUM(v.total),0) AS total,
                COALESCE(SUM(CASE WHEN vp.forma_pago = \'E\' THEN vp.monto ELSE 0 END),0) AS efectivo,
                COALESCE(SUM(CASE WHEN vp.forma_pago = \'T\' THEN vp.monto ELSE 0 END),0) AS transferencia
            ')
            ->leftJoin('venta_pagos as vp', 'vp.venta_id', '=', 'v.venta_id')
            ->where('v.fecha_venta', '>=', $inicio)
            ->where('v.fecha_venta', '<', $fin)
            ->first();
    }

    private function ventasPorDia(string $desde, string $hasta)
    {
        [$inicio, $fin] = $this->rangoFechas($desde, $hasta);

        return DB::table('ventas as v')
            ->selectRaw('v.fecha_venta::date AS fecha, COUNT(v.venta_id) AS cantidad, COALESCE(SUM(v.total),0) AS total')
            ->where('v.fecha_venta', '>=', $inicio)
            ->where('v.fecha_venta', '<', $fin)
            ->groupBy(DB::raw('v.fecha_venta::date'))
            ->orderBy(DB::raw('v.fecha_venta::date'))
            ->get();
    }

    private function resumenFacturacion(string $desde, string $hasta): object
    {
        [$inicio, $fin] = $this->rangoFechas($desde, $hasta);

        return DB::table('facturas as f')
            ->selectRaw('
                COUNT(f.factura_id) AS cantidad,
                COALESCE(SUM(f.total),0) AS total,
                COALESCE(SUM(f.total_iva),0) AS total_iva,
                COALESCE(SUM(f.total_exenta),0) AS total_exenta
            ')
            ->where('f.estado', 'A')
            ->where('f.fecha_emision', '>=', $inicio)
            ->where('f.fecha_emision', '<', $fin)
            ->first();
    }

    private function topProductos(string $desde, string $hasta, int $limite = 10)
    {
        [$inicio, $fin] = $this->rangoFechas($desde, $hasta);

        return DB::table('venta_detalle as vd')
            ->select(
                'p.pro_cod',
                'p.descripcion',
                DB::raw('COALESCE(SUM(vd.cantidad),0) AS cantidad'),
                DB::raw('COALESCE(SUM(vd.subtotal),0) AS total')
            )
            ->join('ventas as v', 'v.venta_id', '=', 'vd.venta_id')
            ->join('productos as p', 'p.pro_cod', '=', 'vd.pro_cod')
            ->where('v.fecha_venta', '>=', $inicio)
            ->where('v.fecha_venta', '<', $fin)
            ->groupBy('p.pro_cod', 'p.descripcion')
            ->orderByDesc(DB::raw('SUM(vd.cantidad)'))
            ->limit($limite)
            ->get();
    }

    private function rangoFechas(string $desde, string $hasta): array
    {
        $inicio = $desde . ' 00:00:00';
        $fin = date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00';

        return [$inicio, $fin];
    }

    private function fechaDesde(Request $request): string
    {
        $fecha = trim((string) $request->input('desde', ''));
        return $this->esFechaValida($fecha) ? $fecha : date('Y-m-01');
    }

    private function fechaHasta(Request $request): string
    {
        $fecha = trim((string) $request->input('hasta', ''));
        return $this->esFechaValida($fecha) ? $fecha : date('Y-m-d');
    }

    private function esFechaValida($fecha): bool
    {
        if ($fecha === '') {
            return false;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}
