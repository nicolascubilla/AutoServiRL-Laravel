<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    public function mostrarImportador()
    {
        return view('productos_importar');
    }

    public function vistaPrevia(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        $archivo = $request->file('archivo');

        if (strtolower($archivo->getClientOriginalExtension()) !== 'xlsx') {
            return redirect()->route('productos.importar')
                ->with('error', 'El archivo debe ser Excel (.xlsx).');
        }

        try {
            $ruta = $archivo->store('imports', 'local');

            $filas = $this->clasificarFilas($this->leerFilas(Storage::disk('local')->path($ruta)));

            if (count($filas) === 0) {
                Storage::disk('local')->delete($ruta);
                return redirect()->route('productos.importar')
                    ->with('error', 'El archivo no contiene filas de datos.');
            }

            session()->put('import_xlsx_ruta', $ruta);

            return view('productos_importar', [
                'filas' => $filas,
                'resumen' => $this->resumenFilas($filas),
            ]);
        } catch (\Exception $e) {
            if (isset($ruta)) {
                Storage::disk('local')->delete($ruta);
            }
            session()->forget('import_xlsx_ruta');

            return redirect()->route('productos.importar')
                ->with('error', 'No fue posible leer el archivo: ' . $e->getMessage());
        }
    }

    public function ejecutar(Request $request)
    {
        $ruta = (string) session()->pull('import_xlsx_ruta');

        if ($ruta === '' || !Storage::disk('local')->exists($ruta)) {
            return redirect()->route('productos.importar')
                ->with('error', 'La sesión de importación ya no existe. Vuelva a cargar el archivo.');
        }

        $insertadas = 0;
        $actualizadas = 0;
        $errores = [];

        try {
            $filas = $this->clasificarFilas($this->leerFilas(Storage::disk('local')->path($ruta)));

            foreach ($filas as $fila) {
                if ($fila['accion'] === 'error') {
                    $errores[] = 'Fila ' . $fila['fila'] . ': ' . $fila['mensaje'];
                    continue;
                }

                try {
                    if ($fila['accion'] === 'actualizar') {
                        $this->actualizarImportado($fila);
                        $actualizadas++;
                    } else {
                        $this->insertar([
                            'codigo' => $fila['codigo'],
                            'codigo_barra' => $fila['codigo_barra'],
                            'descripcion' => $fila['descripcion'],
                            'precio' => $fila['precio'],
                            'tasa_iva' => $fila['tasa_iva'],
                            'cantidad' => $fila['cantidad'] ?? 0,
                            'stock_minimo' => $fila['stock_minimo'],
                        ]);
                        $insertadas++;
                    }
                } catch (\Exception $e) {
                    $errores[] = 'Fila ' . $fila['fila'] . ': ' . $e->getMessage();
                }
            }
        } finally {
            Storage::disk('local')->delete($ruta);
        }

        session()->flash('import_resultado', [
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'errores' => $errores,
        ]);

        return redirect()->route('productos.importar');
    }

    private function actualizarImportado(array $fila): void
    {
        DB::table('productos')
            ->where('codigo_barra', $fila['codigo_barra'])
            ->update([
                'codigo' => $fila['codigo'],
                'descripcion' => $fila['descripcion'],
                'precio' => $fila['precio'],
                'tasa_iva' => $fila['tasa_iva'],
            ]);

        $pro_cod = (int) DB::table('productos')
            ->where('codigo_barra', $fila['codigo_barra'])
            ->value('pro_cod');

        if ($pro_cod <= 0) {
            throw new \RuntimeException('No se encontró el producto para actualizar.');
        }

        $stock = ['stock_minimo' => $fila['stock_minimo']];
        if ($fila['cantidad'] !== null) {
            $stock['cantidad'] = $fila['cantidad'];
        }
        $stock['fecha_actualizacion'] = DB::raw('CURRENT_TIMESTAMP');

        if (DB::table('stock')->where('pro_cod', $pro_cod)->exists()) {
            DB::table('stock')->where('pro_cod', $pro_cod)->update($stock);
        } else {
            DB::table('stock')->insert([
                'pro_cod' => $pro_cod,
                'cantidad' => $fila['cantidad'] ?? 0,
                'stock_minimo' => $fila['stock_minimo'],
                'fecha_actualizacion' => DB::raw('CURRENT_TIMESTAMP'),
            ]);
        }
    }

    private function leerFilas(string $ruta): array
    {
        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(true);
        $libro = $lector->load($ruta);
        $hoja = $libro->getActiveSheet();

        $matriz = $hoja->toArray(null, true, false, false);

        if (count($matriz) === 0) {
            return [];
        }

        $encabezado = array_shift($matriz);

        $mapa = [];
        foreach ($encabezado as $indice => $titulo) {
            if (!is_string($titulo) && !is_numeric($titulo)) {
                continue;
            }
            $clave = $this->normalizarClave((string) $titulo);
            if ($clave === '') {
                continue;
            }
            $campo = $this->campoPara($clave);
            if ($campo !== null && !isset($mapa[$campo])) {
                $mapa[$campo] = $indice;
            }
        }

        foreach (['codigo_barra', 'descripcion', 'precio'] as $requerido) {
            if (!isset($mapa[$requerido])) {
                throw new \RuntimeException('Falta la columna obligatoria "' . $requerido . '" en el encabezado.');
            }
        }

        $filas = [];
        foreach ($matriz as $nro => $celdas) {
            $fila = [];
            $vacia = true;

            foreach ($mapa as $campo => $indice) {
                $valor = $celdas[$indice] ?? null;
                $fila[$campo] = is_string($valor) ? trim($valor) : $valor;
                if (is_string($fila[$campo])) {
                    if ($fila[$campo] !== '') {
                        $vacia = false;
                    }
                } elseif ($fila[$campo] !== null) {
                    $vacia = false;
                }
            }

            if ($vacia) {
                continue;
            }

            $fila['fila'] = $nro + 2;
            $filas[] = $fila;
        }

        return $filas;
    }

    private function clasificarFilas(array $filas): array
    {
        $vistas = [];
        $resultado = [];

        foreach ($filas as $fila) {
            $errores = [];

            $codigoBarra = $this->texto($fila['codigo_barra'] ?? null);
            $descripcion = $this->texto($fila['descripcion'] ?? null);
            $codigo = $this->texto($fila['codigo'] ?? null);

            if ($codigoBarra === null) {
                $errores[] = 'código de barra vacío';
            } elseif ($codigoBarra === '0') {
                $errores[] = 'código de barra no válido';
            }

            if ($descripcion === null) {
                $errores[] = 'descripción vacía';
            }

            $precioNum = $this->parsearNumero($fila['precio'] ?? null);
            if ($precioNum === null || $precioNum < 0) {
                $errores[] = 'precio inválido';
                $precio = null;
            } else {
                $precio = (int) round($precioNum);
            }

            $iva = $this->normalizarIva($fila['iva'] ?? null);
            if ($iva === null) {
                $errores[] = 'IVA inválido (use E, 5 o 10)';
            }

            $cantidad = $this->parsearNumero($fila['cantidad'] ?? null);
            if ($cantidad !== null && $cantidad < 0) {
                $errores[] = 'cantidad negativa';
            }

            $minimo = $this->parsearNumero($fila['stock_minimo'] ?? null);
            if ($minimo === null) {
                $minimo = 0.0;
            }
            if ($minimo < 0) {
                $errores[] = 'stock mínimo negativo';
            }

            $normalizada = [
                'fila' => $fila['fila'],
                'codigo_barra' => $codigoBarra,
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'tasa_iva' => $iva,
                'cantidad' => $cantidad,
                'stock_minimo' => (float) $minimo,
            ];

            if (count($errores) > 0) {
                $normalizada['accion'] = 'error';
                $normalizada['mensaje'] = implode('; ', $errores);
                $resultado[] = $normalizada;
                continue;
            }

            $barra = (string) $codigoBarra;
            $existe = isset($vistas[$barra])
                || DB::table('productos')->where('codigo_barra', $barra)->exists();

            $normalizada['accion'] = $existe ? 'actualizar' : 'insertar';
            $vistas[$barra] = true;
            $resultado[] = $normalizada;
        }

        return $resultado;
    }

    private function resumenFilas(array $filas): array
    {
        $resumen = ['insertar' => 0, 'actualizar' => 0, 'error' => 0];
        foreach ($filas as $fila) {
            $resumen[$fila['accion']]++;
        }

        return $resumen;
    }

    private function normalizarClave(string $valor): string
    {
        $sinAcentos = strtr(mb_strtolower($valor), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return (string) preg_replace('/[^a-z0-9]/', '', $sinAcentos);
    }

    private function campoPara(string $clave): ?string
    {
        $campos = [
            'codigo_barra' => ['codigobarra', 'codigobarras', 'codigodebarra', 'codbarra', 'barcode', 'barra'],
            'codigo' => ['codigo', 'codigointerno', 'codint', 'referencia'],
            'descripcion' => ['descripcion', 'producto', 'nombre', 'articulo', 'detalle'],
            'precio' => ['precio', 'precioventa', 'preciodeventa', 'pv', 'pventa'],
            'iva' => ['iva', 'tasadeiva', 'tasaiva', 'tasa', 'impuesto'],
            'cantidad' => ['cantidad', 'stock', 'stockinicial', 'cantidadinicial', 'cantinicial'],
            'stock_minimo' => ['stockminimo', 'minimo', 'stockmin'],
        ];

        foreach ($campos as $campo => $claves) {
            if (in_array($clave, $claves, true)) {
                return $campo;
            }
        }

        return null;
    }

    private function texto($valor): ?string
    {
        if (is_int($valor)) {
            return (string) $valor;
        }
        if (is_float($valor)) {
            if (floor($valor) == $valor) {
                return (string) (int) $valor;
            }
            return rtrim(rtrim(number_format($valor, 8, '.', ''), '0'), '.');
        }
        if (is_string($valor)) {
            $t = trim($valor);
            return $t === '' ? null : $t;
        }

        return null;
    }

    private function parsearNumero($valor): ?float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }
        if (!is_string($valor)) {
            return null;
        }

        $s = strtolower(trim($valor));
        $s = (string) preg_replace('/gs\.?|g\$|usd|₲/i', '', $s);
        $s = str_replace(' ', '', $s);

        if ($s === '' || $s === '-' || $s === '.' || $s === ',') {
            return null;
        }

        if (strpos($s, ',') !== false || strpos($s, '.') !== false) {
            $ultimaComa = strrpos($s, ',');
            $ultimoPunto = strrpos($s, '.');
            if ($ultimaComa !== false && $ultimoPunto !== false) {
                if ($ultimaComa > $ultimoPunto) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($ultimaComa !== false) {
                if (substr_count($s, ',') > 1) {
                    $s = str_replace(',', '', $s);
                } else {
                    $s = str_replace(',', '.', $s);
                }
            } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', rtrim($s, '.'))) {
                $s = str_replace('.', '', $s);
            }
        }

        if (!is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }

    private function normalizarIva($valor): ?string
    {
        if (is_int($valor) || is_float($valor)) {
            $v = (float) $valor;
            if ($v <= 1.0) {
                $v *= 100;
            }
            if ($v == 0) {
                return 'E';
            }
            if ($v == 5) {
                return '5';
            }
            if ($v == 10) {
                return '10';
            }

            return null;
        }

        if (!is_string($valor)) {
            return null;
        }

        $s = strtolower(trim($valor));
        if ($s === '') {
            return '10';
        }
        if (in_array($s, ['e', 'exenta', 'exento', '0%'], true)) {
            return 'E';
        }
        if (in_array($s, ['5', '5%'], true)) {
            return '5';
        }
        if (in_array($s, ['10', '10%'], true)) {
            return '10';
        }

        $n = $this->parsearNumero($s);
        if ($n === null) {
            return null;
        }
        if ($n == 0) {
            return 'E';
        }
        if ($n == 5) {
            return '5';
        }
        if ($n == 10) {
            return '10';
        }

        return null;
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
