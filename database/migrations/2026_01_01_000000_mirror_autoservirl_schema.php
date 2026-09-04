<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * MIRROR del esquema existente de la base de datos "AutoserviRL" (PostgreSQL).
 *
 * IMPORTANTE: Esta migración es un espejo 1 a 1 del esquema ya existente.
 * Cada tabla se crea sólo si NO existe (Schema::hasTable) para que sea seguro
 * ejecutarla sobre la base de datos compartida sin recrear ni borrar datos.
 * La base de datos live NO debe ejecutar migraciones destructivas (migrate:fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ===================== USUARIOS =====================
        if (!Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->integer('id_usuario')->generatedAs()->primary();
                $table->string('usuario', 50)->unique();
                $table->text('contrasena');
                $table->string('nombre_completo', 100)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('estado', 1)->default('A');
                $table->timestamp('fecha_creacion')->default(\DB::raw('CURRENT_TIMESTAMP'));
            });
        }

        // ===================== FACTURACION CONFIG =====================
        if (!Schema::hasTable('facturacion_config')) {
            Schema::create('facturacion_config', function (Blueprint $table) {
                $table->id('config_id');
                $table->string('nombre_negocio', 200);
                $table->string('actividad', 500)->nullable();
                $table->string('propietario', 200)->nullable();
                $table->string('ruc', 20);
                $table->string('direccion', 300)->nullable();
                $table->string('telefono', 50)->nullable();
                $table->string('ciudad', 100)->nullable();
                $table->string('timbrado', 20);
                $table->date('fecha_inicio_vigencia');
                $table->date('fecha_fin_vigencia');
                $table->string('establecimiento', 3);
                $table->string('punto_expedicion', 3);
                $table->integer('ultimo_numero_factura')->default(0);
            });
        }

        // ===================== PRODUCTOS =====================
        if (!Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->integer('pro_cod')->identity()->primary();
                $table->string('codigo_barra', 50)->unique();
                $table->string('descripcion', 200);
                $table->integer('precio');
                $table->string('activo', 1)->default('S');
                $table->timestamp('fecha_creacion')->default(\DB::raw('CURRENT_TIMESTAMP'));
                $table->string('codigo', 20)->nullable();
                $table->string('tasa_iva', 2)->default('10');
            });
        }

        // ===================== STOCK =====================
        if (!Schema::hasTable('stock')) {
            Schema::create('stock', function (Blueprint $table) {
                $table->id('stock_id');
                $table->integer('pro_cod');
                $table->decimal('cantidad', 12, 3)->default(0);
                $table->decimal('stock_minimo', 12, 3)->default(0);
                $table->timestamp('fecha_actualizacion')->default(\DB::raw('CURRENT_TIMESTAMP'));

                $table->unique('pro_cod');
                $table->foreign('pro_cod')->references('pro_cod')->on('productos');
            });
        }

        // ===================== STOCK MOVIMIENTOS =====================
        if (!Schema::hasTable('stock_movimientos')) {
            Schema::create('stock_movimientos', function (Blueprint $table) {
                $table->bigInteger('mov_id')->generatedAs()->primary();
                $table->integer('pro_cod');
                $table->string('tipo', 10);
                $table->decimal('cantidad', 12, 3);
                $table->decimal('stock_resultante', 12, 3);
                $table->string('observacion', 255)->nullable();
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_movimiento')->default(\DB::raw('CURRENT_TIMESTAMP'));

                $table->foreign('pro_cod')->references('pro_cod')->on('productos');
                $table->foreign('usuario_id')->references('id_usuario')->on('usuarios');
            });
        }

        // ===================== CAJAS =====================
        if (!Schema::hasTable('cajas')) {
            Schema::create('cajas', function (Blueprint $table) {
                $table->id('caja_id');
                $table->integer('usuario_apertura_id');
                $table->timestamp('fecha_apertura')->default(\DB::raw('CURRENT_TIMESTAMP'));
                $table->integer('monto_inicial')->default(0);
                $table->timestamp('fecha_cierre')->nullable();
                $table->integer('usuario_cierre_id')->nullable();
                $table->integer('monto_cierre')->nullable();
                $table->string('estado', 1)->default('A');
                $table->string('observacion', 500)->nullable();
                $table->integer('monto_esperado')->nullable();
                $table->integer('diferencia')->nullable();

                $table->foreign('usuario_apertura_id')->references('id_usuario')->on('usuarios');
                $table->foreign('usuario_cierre_id')->references('id_usuario')->on('usuarios');
                $table->index(['usuario_apertura_id', 'estado']);
            });
        }

        // ===================== VENTAS =====================
        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id('venta_id');
                $table->unsignedBigInteger('caja_id');
                $table->integer('usuario_id');
                $table->timestamp('fecha_venta')->default(\DB::raw('CURRENT_TIMESTAMP'));
                $table->integer('total')->default(0);
                $table->string('estado', 1)->default('P');
                $table->string('observacion', 500)->nullable();

                $table->foreign('caja_id')->references('caja_id')->on('cajas');
                $table->foreign('usuario_id')->references('id_usuario')->on('usuarios');
                $table->index('caja_id');
                $table->index('estado');
                $table->index('fecha_venta');
                $table->index('usuario_id');
            });
        }

        // ===================== VENTA DETALLE =====================
        if (!Schema::hasTable('venta_detalle')) {
            Schema::create('venta_detalle', function (Blueprint $table) {
                $table->id('venta_detalle_id');
                $table->unsignedBigInteger('venta_id');
                $table->integer('pro_cod');
                $table->decimal('cantidad', 12, 3);
                $table->integer('precio_unitario');
                $table->integer('subtotal');

                $table->foreign('venta_id')->references('venta_id')->on('ventas');
                $table->foreign('pro_cod')->references('pro_cod')->on('productos');
                $table->index('pro_cod');
                $table->index('venta_id');
            });
        }

        // ===================== VENTA PAGOS =====================
        if (!Schema::hasTable('venta_pagos')) {
            Schema::create('venta_pagos', function (Blueprint $table) {
                $table->id('venta_pago_id');
                $table->unsignedBigInteger('venta_id');
                $table->string('forma_pago', 1);
                $table->integer('monto');

                $table->foreign('venta_id')->references('venta_id')->on('ventas');
                $table->index('venta_id');
            });
        }

        // ===================== MOVIMIENTOS CAJA =====================
        if (!Schema::hasTable('movimientos_caja')) {
            Schema::create('movimientos_caja', function (Blueprint $table) {
                $table->id('movimiento_id');
                $table->unsignedBigInteger('caja_id');
                $table->integer('usuario_id');
                $table->unsignedBigInteger('venta_id')->nullable();
                $table->timestamp('fecha_movimiento')->default(\DB::raw('CURRENT_TIMESTAMP'));
                $table->string('tipo', 1);
                $table->string('forma_pago', 1)->nullable();
                $table->integer('monto');
                $table->string('descripcion', 500)->nullable();

                $table->foreign('caja_id')->references('caja_id')->on('cajas');
                $table->foreign('usuario_id')->references('id_usuario')->on('usuarios');
                $table->foreign('venta_id')->references('venta_id')->on('ventas');
                $table->index('caja_id');
                $table->index('fecha_movimiento');
                $table->index('usuario_id');
                $table->index('venta_id');
            });
        }

        // ===================== FACTURAS =====================
        if (!Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $table) {
                $table->id('factura_id');
                $table->unsignedBigInteger('venta_id');
                $table->timestamp('fecha_emision')->default(\DB::raw('CURRENT_TIMESTAMP'));
                $table->integer('numero_factura');
                $table->string('establecimiento', 3);
                $table->string('punto_expedicion', 3);
                $table->string('timbrado', 20);
                $table->string('cliente_nombre', 200);
                $table->string('cliente_documento', 30);
                $table->string('cliente_direccion', 300)->nullable();
                $table->string('cliente_telefono', 50)->nullable();
                $table->string('condicion_venta', 1)->default('C');
                $table->integer('total');
                $table->integer('total_exenta')->default(0);
                $table->integer('total_iva_5')->default(0);
                $table->integer('total_iva_10')->default(0);
                $table->integer('total_iva')->default(0);
                $table->string('estado', 1)->default('A');

                $table->foreign('venta_id')->references('venta_id')->on('ventas');
            });
        }

        // ===================== FACTURA DETALLE =====================
        if (!Schema::hasTable('factura_detalle')) {
            Schema::create('factura_detalle', function (Blueprint $table) {
                $table->id('factura_detalle_id');
                $table->unsignedBigInteger('factura_id');
                $table->unsignedBigInteger('pro_cod')->nullable();
                $table->string('descripcion', 300);
                $table->decimal('cantidad', 12, 2);
                $table->integer('precio_unitario');
                $table->integer('exenta')->default(0);
                $table->integer('gravada_5')->default(0);
                $table->integer('gravada_10')->default(0);
                $table->integer('subtotal');

                $table->foreign('factura_id')->references('factura_id')->on('facturas');
                $table->index('factura_id');
            });
        }
    }

    public function down(): void
    {
        // La réplica comparte la base de datos live: NO se permiten borrados.
        // down() queda vacío para prevenir borrado accidental de datos.
    }
};
