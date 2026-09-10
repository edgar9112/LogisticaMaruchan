<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevolucionesController;
use App\Http\Controllers\EmbarquesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\RecepcionesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TrazabilidadController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ViajesController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:admin,ventas,almacen,logistica'])->group(function () {
        Route::get('/trazabilidad', [TrazabilidadController::class, 'index'])->name('trazabilidad.index');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('presentaciones', PresentationController::class)
            ->parameters(['presentaciones' => 'presentation'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('tiendas', StoreController::class)
            ->parameters(['tiendas' => 'store'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('almacenes', WarehouseController::class)
            ->parameters(['almacenes' => 'warehouse'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('vehiculos', VehicleController::class)
            ->parameters(['vehiculos' => 'vehicle'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('clientes', CustomerController::class)
            ->parameters(['clientes' => 'customer'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        $reportTypes = implode('|', ['pedidos', 'embarques', 'incidencias', 'movimientos']);

        Route::get('/reportes', [ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/{reporte}', [ReportController::class, 'show'])
            ->where('reporte', $reportTypes)
            ->name('reportes.show');
        Route::get('/reportes/{reporte}/pdf', [ReportController::class, 'pdf'])
            ->where('reporte', $reportTypes)
            ->name('reportes.pdf');
        Route::get('/reportes/{reporte}/excel', [ReportController::class, 'excel'])
            ->where('reporte', $reportTypes)
            ->name('reportes.excel');

        Route::post('almacenes/{warehouse}/locations', [WarehouseController::class, 'storeLocation'])
            ->name('almacenes.locations.store');
        Route::delete('locations/{location}', [WarehouseController::class, 'destroyLocation'])
            ->name('almacenes.locations.destroy');
    });

    Route::middleware(['role:ventas,admin'])->group(function () {
        Route::get('/ventas/pedidos', [VentasController::class, 'index'])->name('ventas.pedidos');
        Route::get('/ventas/pedidos/create', [VentasController::class, 'create'])->name('ventas.pedidos.create');
        Route::post('/ventas/pedidos', [VentasController::class, 'store'])->name('ventas.pedidos.store');
        Route::get('/ventas/pedidos/{order}', [VentasController::class, 'show'])->name('ventas.pedidos.show');
    });

    Route::middleware(['role:almacen,admin'])->group(function () {
        Route::get('/almacen/pendientes', [AlmacenController::class, 'pendientes'])->name('almacen.pendientes');
        Route::get('/almacen/pedidos/{order}', [AlmacenController::class, 'show'])->name('almacen.pedidos.show');
        Route::get('/almacen/pedidos/{order}/recibir', [AlmacenController::class, 'recibir'])->name('almacen.pedidos.recibir');
        Route::post('/almacen/pedidos/{order}/entrada', [AlmacenController::class, 'guardarEntrada'])->name('almacen.pedidos.entrada');
        Route::post('/almacen/pedidos/{order}/confirmar', [AlmacenController::class, 'confirmarEntrada'])->name('almacen.pedidos.confirmar');
        Route::get('/almacen/pedidos/{order}/clasificar', [AlmacenController::class, 'clasificar'])->name('almacen.pedidos.clasificar');
        Route::post('/almacen/pedidos/{order}/clasificar', [AlmacenController::class, 'guardarClasificacion'])->name('almacen.pedidos.clasificar.guardar');
        Route::get('/almacen/pedidos/{order}/preparar', [AlmacenController::class, 'preparar'])->name('almacen.pedidos.preparar');
        Route::post('/almacen/pedidos/{order}/preparar', [AlmacenController::class, 'guardarPreparacion'])->name('almacen.pedidos.preparar.guardar');
    });

    Route::middleware(['role:logistica,admin'])->group(function () {
        Route::get('/embarques', [EmbarquesController::class, 'index'])->name('embarques.index');
        Route::get('/embarques/create', [EmbarquesController::class, 'create'])->name('embarques.create');
        Route::post('/embarques', [EmbarquesController::class, 'store'])->name('embarques.store');
        Route::get('/embarques/{shipment}', [EmbarquesController::class, 'show'])->name('embarques.show');
        Route::post('/embarques/{shipment}/cargar', [EmbarquesController::class, 'cargar'])->name('embarques.cargar');
    });

    Route::middleware(['role:transporte,admin'])->group(function () {
        Route::get('/viajes', [ViajesController::class, 'index'])->name('viajes.index');
        Route::get('/viajes/{shipment}', [ViajesController::class, 'show'])->name('viajes.show');
        Route::post('/viajes/{shipment}/salir', [ViajesController::class, 'salir'])->name('viajes.salir');
        Route::post('/viajes/{shipment}/llegar', [ViajesController::class, 'llegar'])->name('viajes.llegar');
        Route::post('/viajes/{shipment}/incidencia', [ViajesController::class, 'registrarIncidencia'])->name('viajes.incidencia');
    });

    Route::middleware(['role:tienda,admin'])->group(function () {
        Route::get('/tienda/recepciones', [RecepcionesController::class, 'index'])->name('tienda.recepciones');
        Route::post('/tienda/recepciones/{order}/confirmar', [RecepcionesController::class, 'confirmar'])->name('tienda.recepciones.confirmar');
    });

    Route::middleware(['role:tienda,almacen,admin'])->group(function () {
        Route::get('/devoluciones', [DevolucionesController::class, 'index'])->name('devoluciones.index');
        Route::get('/devoluciones/{devolucion}', [DevolucionesController::class, 'show'])->name('devoluciones.show');
    });

    Route::middleware(['role:tienda,admin'])->group(function () {
        Route::get('/tienda/pedidos/{order}/devolucion', [DevolucionesController::class, 'create'])->name('devoluciones.create');
        Route::post('/tienda/pedidos/{order}/devolucion', [DevolucionesController::class, 'store'])->name('devoluciones.store');
    });

    Route::middleware(['role:almacen,admin'])->group(function () {
        Route::post('/devoluciones/{devolucion}/recibir', [DevolucionesController::class, 'recibir'])->name('devoluciones.recibir');
    });
});