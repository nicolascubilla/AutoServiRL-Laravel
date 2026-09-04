<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FlujoCompletoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_flujo_venta_factura_caja(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $hash = password_hash('clave123', PASSWORD_BCRYPT);
        DB::table('usuarios')->insert([
            'usuario' => 'test_laravel',
            'contrasena' => $hash,
            'nombre_completo' => 'Usuario Prueba',
            'email' => 'test@test.com',
            'estado' => 'A',
        ]);
        $uid = (int) DB::table('usuarios')->where('usuario', 'test_laravel')->value('id_usuario');

        $this->assertGreaterThan(0, $uid);

        $userModel = \App\Models\User::find($uid);
        $this->actingAs($userModel);
        $this->withSession([
            'user_id' => $uid,
            'usuario' => 'test_laravel',
            'nombre' => 'Usuario Prueba',
        ]);

        // --- Dashboard ---
        $this->get('/')->assertStatus(200);

        // --- Crear producto ---
        $this->post('/productos/guardar', [
            'codigo' => 'TEST01',
            'codigo_barra' => '9000000000001',
            'descripcion' => 'Producto de Prueba',
            'precio' => '10000',
            'tasa_iva' => '10',
            'cantidad' => '50',
            'stock_minimo' => '5',
        ])->assertRedirect('/productos');

        $producto = DB::table('productos')->where('codigo_barra', '9000000000001')->first();
        $this->assertNotNull($producto);
        $pro_cod = (int) $producto->pro_cod;

        // --- Buscar por código de barra (JSON) ---
        $resp = $this->getJson('/producto/buscar-barra?codigo_barra=9000000000001');
        $resp->assertOk()->assertJsonPath('success', true);
        $this->assertEquals($pro_cod, (int) $resp->json('producto.pro_cod'));

        // --- Stock: entrada adicional + mínimo ---
        $this->post('/stock/entrada', ['pro_cod' => $pro_cod, 'cantidad' => '10', 'observacion' => 'Reposicion test'])
            ->assertRedirect('/stock');
        $this->post('/stock/minimo', ['pro_cod' => $pro_cod, 'stock_minimo' => '3'])
            ->assertRedirect('/stock');
        $stock = DB::table('stock')->where('pro_cod', $pro_cod)->first();
        $this->assertEquals(60, (float) $stock->cantidad);
        $this->assertEquals(3, (float) $stock->stock_minimo);

        // --- Abrir caja ---
        $this->post('/caja/abrir', ['monto_inicial' => '50000'])->assertRedirect('/caja');
        $caja = DB::table('cajas')->where('usuario_apertura_id', $uid)->where('estado', 'A')->first();
        $this->assertNotNull($caja);
        $caja_id = (int) $caja->caja_id;

        // --- Página de ventas (con caja abierta) ---
        $this->get('/ventas')->assertStatus(200);

        // --- Venta POS ---
        $respVenta = $this->postJson('/venta/guardar', [
            'carrito' => [
                ['pro_cod' => $pro_cod, 'codigo_barra' => '9000000000001', 'descripcion' => 'Producto de Prueba', 'precio' => 10000, 'cantidad' => 2, 'subtotal' => 20000],
                ['pro_cod' => $pro_cod, 'cantidad' => 1, 'precio' => 10000, 'subtotal' => 10000],
            ],
            'forma_pago' => 'E',
            'monto_recibido' => 50000,
        ]);
        $respVenta->assertOk()->assertJsonPath('success', true);
        $venta_id = (int) $respVenta->json('venta_id');
        $this->assertGreaterThan(0, $venta_id);
        $this->assertEquals(30000, (int) $respVenta->json('total'));
        $this->assertEquals(20000, (int) $respVenta->json('vuelto'));

        // Verificaciones de la venta en BD
        $venta = DB::table('ventas')->where('venta_id', $venta_id)->first();
        $this->assertEquals(30000, (int) $venta->total);
        $this->assertEquals('P', $venta->estado);
        $this->assertEquals($caja_id, (int) $venta->caja_id);
        $this->assertEquals(3, DB::table('venta_detalle')->where('venta_id', $venta_id)->sum('cantidad'));
        $this->assertEquals(30000, DB::table('venta_pagos')->where('venta_id', $venta_id)->value('monto'));
        $this->assertEquals(1, DB::table('movimientos_caja')->where('venta_id', $venta_id)->where('tipo', 'I')->count());
        // stock descontado 60 - 3 = 57
        $this->assertEquals(57, (float) DB::table('stock')->where('pro_cod', $pro_cod)->value('cantidad'));

        // --- Ticket ---
        $this->get('/ticket/' . $venta_id)->assertStatus(200);

        // --- Historial de ventas ---
        $this->get('/ventas/historial')->assertStatus(200)->assertSee('#'. $venta_id);

        // --- Facturación ---
        $this->get('/facturacion/' . $venta_id)->assertStatus(200);

        $respFactura = $this->postJson('/factura/emitir', [
            'venta_id' => $venta_id,
            'cliente_documento' => '1234567-8',
            'cliente_nombre' => 'Cliente de Prueba',
        ]);
        $respFactura->assertOk()->assertJsonPath('success', true);
        $factura_id = (int) $respFactura->json('factura_id');
        $this->assertGreaterThan(0, $factura_id);

        $factura = DB::table('facturas')->where('factura_id', $factura_id)->first();
        $this->assertEquals('A', $factura->estado);
        $this->assertEquals(30000, (int) $factura->total);
        $this->assertEquals(round(30000 / 11), (int) $factura->total_iva_10);
        $this->assertEquals($venta_id, (int) $factura->venta_id);

        // Config: ultimo_numero_factura avanzado
        $config = DB::table('facturacion_config')->orderBy('config_id')->first();
        $this->assertEquals($factura->numero_factura, (int) $config->ultimo_numero_factura);

        // --- Ver / Imprimir factura ---
        $this->get('/factura/ver/' . $factura_id)->assertStatus(200);
        $this->get('/factura/imprimir/' . $factura_id)->assertStatus(200);

        // --- Historial de facturas ---
        $this->get('/factura/historial')->assertStatus(200)->assertSee('Cliente de Prueba');

        // No facturar dos veces la misma venta
        $respDoble = $this->postJson('/factura/emitir', [
            'venta_id' => $venta_id,
            'cliente_documento' => '1234567-8',
            'cliente_nombre' => 'Otro Cliente',
        ]);
        $this->assertFalse($respDoble->json('success'));

        // --- Cerrar caja ---
        $this->post('/caja/cerrar', [
            'caja_id' => $caja_id,
            'monto_cierre' => '85000',
            'observacion' => 'Cierre de prueba',
        ])->assertRedirect('/caja');
        $cajaCerrada = DB::table('cajas')->where('caja_id', $caja_id)->first();
        $this->assertEquals('C', $cajaCerrada->estado);

        // --- Historial de cajas + detalle ---
        $this->get('/caja/historial')->assertStatus(200);
        $this->get('/caja/detalle/' . $caja_id)->assertStatus(200);

        // --- Informes ---
        $this->get('/informes')->assertStatus(200);
        $this->get('/informes/imprimir')->assertStatus(200);
        // El total de ventas del informe debe incluir la venta de prueba
        $this->get('/informes')->assertSee('3');

        // --- Exportar CSV ---
        $this->get('/informes/exportar?tipo=ventas')->assertOk();
        $this->get('/informes/exportar?tipo=productos')->assertOk();

        // --- Cambiar contraseña ---
        $this->get('/cambiar-contrasena')->assertStatus(200);
        $this->post('/cambiar-contrasena', ['actual' => 'clave123', 'nueva' => 'nueva123', 'repite' => 'nueva123'])
            ->assertRedirect('/cambiar-contrasena');
        $nuevoHash = DB::table('usuarios')->where('id_usuario', $uid)->value('contrasena');
        $this->assertTrue(password_verify('nueva123', $nuevoHash));

        // --- Salida de stock adversa (stock insuficiente) ---
        $this->post('/stock/salida', ['pro_cod' => $pro_cod, 'cantidad' => '99999', 'observacion' => ''])->assertRedirect('/stock');
        // No debe haberse registrado movimiento
        $this->assertEquals(0, DB::table('stock_movimientos')
            ->where('pro_cod', $pro_cod)->where('tipo', 'salida')->where('observacion', '')->count());
    }
}