<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Áreas protegidas (autenticadas)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
    Route::post('/productos/guardar', [ProductoController::class, 'guardar'])->name('productos.guardar');
    Route::post('/productos/estado', [ProductoController::class, 'cambiarEstado'])->name('productos.estado');

    // Stock
    Route::get('/stock', [StockController::class, 'index'])->name('stock');
    Route::post('/stock/entrada', [StockController::class, 'entrada'])->name('stock.entrada');
    Route::post('/stock/salida', [StockController::class, 'salida'])->name('stock.salida');
    Route::post('/stock/minimo', [StockController::class, 'minimo'])->name('stock.minimo');
    Route::get('/stock/historial', [StockController::class, 'historial'])->name('stock.historial');

    // Caja
    Route::get('/caja', [CajaController::class, 'index'])->name('caja');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('/caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
    Route::get('/caja/detalle/{caja}', [CajaController::class, 'detalle'])->name('caja.detalle');

    // Ventas (POS + historial + ticket)
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas');
    Route::get('/ventas/historial', [VentaController::class, 'listar'])->name('ventas.historial');
    Route::get('/producto/buscar-barra', [VentaController::class, 'buscarPorCodigoBarra'])->name('ventas.buscar_barra');
    Route::post('/venta/guardar', [VentaController::class, 'guardar'])->name('venta.guardar');
    Route::post('/venta/anular', [VentaController::class, 'anular'])->name('venta.anular');
    Route::get('/ticket/{venta}', [TicketController::class, 'mostrar'])->name('ticket');

    // Facturación
    Route::get('/facturacion/config', [FacturacionController::class, 'index'])->name('facturacion.config');
    Route::post('/facturacion/config/actualizar', [FacturacionController::class, 'actualizar'])->name('facturacion.config.actualizar');
    Route::get('/cliente/buscar-ruc', [FacturacionController::class, 'buscarRuc'])->name('cliente.buscar_ruc');
    Route::get('/facturacion/{venta}', [FacturacionController::class, 'facturarVenta'])->name('facturacion');
    Route::post('/factura/emitir', [FacturacionController::class, 'emitirFactura'])->name('factura.emitir');
    Route::get('/factura/ver/{factura}', [FacturacionController::class, 'verFactura'])->name('factura.ver');
    Route::get('/factura/imprimir/{factura}', [FacturacionController::class, 'imprimirFactura'])->name('factura.imprimir');
    Route::get('/factura/historial', [FacturacionController::class, 'listarFacturas'])->name('factura.historial');
    Route::get('/cotizacion', [FacturacionController::class, 'cotizacion'])->name('cotizacion');

    // Informes
    Route::get('/informes', [InformeController::class, 'index'])->name('informes');
    Route::get('/informes/exportar', [InformeController::class, 'exportarCsv'])->name('informes.exportar');
    Route::get('/informes/imprimir', [InformeController::class, 'imprimir'])->name('informes.imprimir');

    // Usuario
    Route::get('/cambiar-contrasena', [UsuarioController::class, 'cambiarContrasenaForm'])->name('cambiar_contrasena');
    Route::post('/cambiar-contrasena', [UsuarioController::class, 'cambiarContrasena'])->name('cambiar_contrasena_guardar');
});
