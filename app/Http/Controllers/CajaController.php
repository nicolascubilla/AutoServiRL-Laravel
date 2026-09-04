<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index()
    {
        $usuario_id = (int) session('user_id');

        $cajaActual = DB::table('cajas as c')
            ->select('c.*', 'u.nombre_completo')
            ->join('usuarios as u', 'u.id_usuario', '=', 'c.usuario_apertura_id')
            ->where('c.usuario_apertura_id', $usuario_id)
            ->where('c.estado', 'A')
            ->orderByDesc('c.caja_id')
            ->first();

        $resumenCaja = $cajaActual ? $this->resumenCaja($cajaActual->caja_id) : null;

        return view('caja', ['cajaActual' => $cajaActual, 'resumenCaja' => $resumenCaja]);
    }

    public function abrir(Request $request)
    {
        $usuario_id = (int) session('user_id');
        $monto_inicial = (int) preg_replace('/[^0-9]/', '', (string) $request->input('monto_inicial', 0));

        $caja = DB::table('cajas')
            ->where('usuario_apertura_id', $usuario_id)
            ->where('estado', 'A')
            ->first();

        if ($caja) {
            return redirect()->route('caja')
                ->with('error', 'Ya existe una caja abierta para este usuario.');
        }

        DB::table('cajas')->insert([
            'usuario_apertura_id' => $usuario_id,
            'monto_inicial' => $monto_inicial,
        ]);

        return redirect()->route('caja')->with('success', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request)
    {
        $usuario_id = (int) session('user_id');
        $caja_id = (int) $request->input('caja_id', 0);
        $monto_cierre = (int) preg_replace('/[^0-9]/', '', (string) $request->input('monto_cierre', 0));
        $observacion = trim((string) $request->input('observacion', ''));

        $afectadas = DB::table('cajas')
            ->where('caja_id', $caja_id)
            ->where('estado', 'A')
            ->update([
                'fecha_cierre' => DB::raw('CURRENT_TIMESTAMP'),
                'usuario_cierre_id' => $usuario_id,
                'monto_cierre' => $monto_cierre,
                'estado' => 'C',
                'observacion' => $observacion !== '' ? $observacion : null,
            ]);

        if ($afectadas > 0) {
            return redirect()->route('caja')->with('success', 'Caja cerrada correctamente.');
        }

        return redirect()->route('caja')->with('error', 'No fue posible cerrar la caja.');
    }

    public function historial()
    {
        $historialCajas = DB::table('cajas as c')
            ->select(
                'c.caja_id',
                'c.fecha_apertura',
                'c.fecha_cierre',
                'c.monto_inicial',
                'c.monto_cierre',
                'c.estado',
                'c.observacion',
                'ua.nombre_completo AS usuario_apertura',
                'uc.nombre_completo AS usuario_cierre'
            )
            ->join('usuarios as ua', 'ua.id_usuario', '=', 'c.usuario_apertura_id')
            ->leftJoin('usuarios as uc', 'uc.id_usuario', '=', 'c.usuario_cierre_id')
            ->where('c.estado', 'C')
            ->orderByDesc('c.fecha_cierre')
            ->orderByDesc('c.caja_id')
            ->get();

        return view('caja_historial', ['historialCajas' => $historialCajas]);
    }

    public function detalle($caja)
    {
        $caja_id = (int) $caja;

        $caja = DB::table('cajas as c')
            ->select(
                'c.caja_id',
                'c.fecha_apertura',
                'c.fecha_cierre',
                'c.monto_inicial',
                'c.monto_cierre',
                'c.estado',
                'c.observacion',
                'ua.nombre_completo AS usuario_apertura',
                'uc.nombre_completo AS usuario_cierre'
            )
            ->join('usuarios as ua', 'ua.id_usuario', '=', 'c.usuario_apertura_id')
            ->leftJoin('usuarios as uc', 'uc.id_usuario', '=', 'c.usuario_cierre_id')
            ->where('c.caja_id', $caja_id)
            ->where('c.estado', 'C')
            ->first();

        if (!$caja) {
            return view('caja_detalle', ['detalleCaja' => null]);
        }

        $resumen = DB::table('movimientos_caja')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN tipo='I' AND forma_pago='E' THEN monto ELSE 0 END),0) AS ingresos_efectivo,
                COALESCE(SUM(CASE WHEN tipo='I' AND forma_pago='T' THEN monto ELSE 0 END),0) AS ingresos_transferencia,
                COALESCE(SUM(CASE WHEN tipo='E' THEN monto ELSE 0 END),0) AS egresos
            ")
            ->where('caja_id', $caja_id)
            ->first();

        $montoEsperado = (int) $caja->monto_inicial + (int) $resumen->ingresos_efectivo - (int) $resumen->egresos;
        $diferencia = (int) $caja->monto_cierre - $montoEsperado;

        $detalleCaja = [
            'caja' => $caja,
            'resumen' => [
                'monto_inicial' => (int) $caja->monto_inicial,
                'ingresos_efectivo' => (int) $resumen->ingresos_efectivo,
                'ingresos_transferencia' => (int) $resumen->ingresos_transferencia,
                'egresos' => (int) $resumen->egresos,
                'monto_esperado' => $montoEsperado,
                'monto_cierre' => (int) $caja->monto_cierre,
                'diferencia' => $diferencia,
            ],
        ];

        return view('caja_detalle', ['detalleCaja' => $detalleCaja]);
    }

    private function resumenCaja(int $caja_id): ?object
    {
        $resumen = DB::table('cajas as c')
            ->selectRaw("
                c.monto_inicial,
                COALESCE(SUM(CASE WHEN mc.tipo='I' AND mc.forma_pago='E' THEN mc.monto ELSE 0 END),0) AS ingresos_efectivo,
                COALESCE(SUM(CASE WHEN mc.tipo='I' AND mc.forma_pago='T' THEN mc.monto ELSE 0 END),0) AS ingresos_transferencia,
                COALESCE(SUM(CASE WHEN mc.tipo='E' THEN mc.monto ELSE 0 END),0) AS egresos
            ")
            ->leftJoin('movimientos_caja as mc', 'mc.caja_id', '=', 'c.caja_id')
            ->where('c.caja_id', $caja_id)
            ->groupBy('c.caja_id', 'c.monto_inicial')
            ->first();

        if (!$resumen) {
            return null;
        }

        $resumen->monto_inicial = (int) $resumen->monto_inicial;
        $resumen->ingresos_efectivo = (int) $resumen->ingresos_efectivo;
        $resumen->ingresos_transferencia = (int) $resumen->ingresos_transferencia;
        $resumen->egresos = (int) $resumen->egresos;
        $resumen->monto_esperado = $resumen->monto_inicial + $resumen->ingresos_efectivo - $resumen->egresos;

        return $resumen;
    }
}
