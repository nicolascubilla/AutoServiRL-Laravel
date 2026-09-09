<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductoImportacionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_importa_productos_desde_xlsx_con_vista_previa(): void
    {
        if (! Schema::hasTable('productos')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario());
        $this->actingAs($admin);

        $sufijo = random_int(100000, 999999);

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->fromArray([
            ['codigo barra', 'codigo', 'descripcion', 'precio', 'iva', 'cantidad', 'stock minimo'],
            [$sufijo . '001', 'C-1', 'Filtro de aceite', 45000, '10', 20, 5],
            [$sufijo . '002', '', 'Filtro de aire', '15000', '5', '10', '2'],
            [$sufijo . '003', '', '', 1000, '10', 1, 1],
            ['IVA MALO', '', 'IVA incorrecto', 1000, 'X', 1, 1],
        ]);
        $hoja->setCellValueExplicit('D3', '15.000', DataType::TYPE_STRING);

        $ruta = tempnam(sys_get_temp_dir(), 'imp_') . '.xlsx';
        (new Xlsx($libro))->save($ruta);

        $archivo = new UploadedFile($ruta, 'productos.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        try {
            $vista = $this->post(route('productos.importar.vista'), ['archivo' => $archivo]);
            $vista->assertOk();
            $vista->assertViewHas('resumen');
            $resumen = $vista->viewData('resumen');

            $this->assertSame(2, $resumen['insertar']);
            $this->assertSame(2, $resumen['error']);

            $this->assertSame(0, DB::table('productos')->where('codigo_barra', $sufijo . '001')->count(), 'La vista previa no debe escribir en la BD.');

            $ejecutar = $this->post(route('productos.importar.ejecutar'), []);
            $ejecutar->assertRedirect(route('productos.importar'));
            $ejecutar->assertSessionHas('import_resultado');

            $resultado = session('import_resultado');
            $this->assertSame(2, $resultado['insertadas']);
            $this->assertSame(0, $resultado['actualizadas']);
            $this->assertCount(2, $resultado['errores']);
        } finally {
            @unlink($ruta);
        }

        $this->assertSame(1, DB::table('productos')->where('codigo_barra', $sufijo . '001')->count());
        $producto = DB::table('productos')->where('codigo_barra', $sufijo . '001')->first();
        $this->assertSame(45000, (int) $producto->precio);
        $this->assertSame('10', (string) $producto->tasa_iva);
        $this->assertSame('Filtro de aceite', (string) $producto->descripcion);
        $this->assertSame('C-1', (string) $producto->codigo);

        $stock = DB::table('stock')->where('pro_cod', $producto->pro_cod)->first();
        $this->assertNotNull($stock);
        $this->assertSame(20.0, (float) $stock->cantidad);
        $this->assertSame(5.0, (float) $stock->stock_minimo);

        $producto2 = DB::table('productos')->where('codigo_barra', $sufijo . '002')->first();
        $this->assertNotNull($producto2);
        $this->assertSame(15000, (int) $producto2->precio);
        $this->assertSame('5', (string) $producto2->tasa_iva);
        $this->assertSame(10, (int) DB::table('stock')->where('pro_cod', $producto2->pro_cod)->value('cantidad'));
        $this->assertSame(2.0, (float) DB::table('stock')->where('pro_cod', $producto2->pro_cod)->value('stock_minimo'));

        $this->assertSame(0, DB::table('productos')->where('codigo_barra', $sufijo . '003')->count(), 'Fila sin descripción no debe insertarse.');
        $this->assertSame(0, DB::table('productos')->where('codigo_barra', 'IVA MALO')->count(), 'Fila con IVA inválido no debe insertarse.');
    }

    public function test_actualiza_producto_existente_por_codigo_de_barra(): void
    {
        if (! Schema::hasTable('productos')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario());
        $this->actingAs($admin);

        $sufijo = random_int(100000, 999999);
        $barra = $sufijo . '001';

        $pro_cod = (int) DB::table('productos')->insertGetId([
            'codigo_barra' => $barra,
            'descripcion' => 'Producto viejo',
            'precio' => 1000,
            'tasa_iva' => 'E',
            'activo' => 'S',
        ], 'pro_cod');
        DB::table('stock')->insert(['pro_cod' => $pro_cod, 'cantidad' => 7, 'stock_minimo' => 3]);

        $libro = new Spreadsheet();
        $libro->getActiveSheet()->fromArray([
            ['codigo de barra', 'descripcion', 'precio', 'iva', 'cantidad', 'stock minimo'],
            [$barra, 'Producto nuevo', '2000', '10', '15', '4'],
        ]);

        $ruta = tempnam(sys_get_temp_dir(), 'imp_') . '.xlsx';
        (new Xlsx($libro))->save($ruta);
        $archivo = new UploadedFile($ruta, 'productos.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        try {
            $vista = $this->post(route('productos.importar.vista'), ['archivo' => $archivo]);
            $vista->assertOk();
            $resumen = $vista->viewData('resumen');
            $this->assertSame(1, $resumen['actualizar']);

            $this->post(route('productos.importar.ejecutar'), []);

            $producto = DB::table('productos')->where('pro_cod', $pro_cod)->first();
            $this->assertSame('Producto nuevo', (string) $producto->descripcion);
            $this->assertSame(2000, (int) $producto->precio);
            $this->assertSame('10', (string) $producto->tasa_iva);
            $this->assertSame(15.0, (float) DB::table('stock')->where('pro_cod', $pro_cod)->value('cantidad'));
            $this->assertSame(4.0, (float) DB::table('stock')->where('pro_cod', $pro_cod)->value('stock_minimo'));
            $this->assertSame(1, DB::table('productos')->where('codigo_barra', $barra)->count());
        } finally {
            @unlink($ruta);
        }
    }

    private function crearUsuario(): int
    {
        return (int) DB::table('usuarios')->insertGetId([
            'usuario' => 'admin_imp_' . random_int(100000, 999999),
            'contrasena' => bcrypt('clave_admin'),
            'nombre_completo' => 'Admin Import',
            'email' => 'admin_imp_' . random_int(100000, 999999) . '@test.com',
            'estado' => 'A',
        ], 'id_usuario');
    }
}