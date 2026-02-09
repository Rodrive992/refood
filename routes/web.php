<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\CartaController;
use App\Http\Controllers\Admin\MesaController as AdminMesaController;
use App\Http\Controllers\Admin\ComandaController as AdminComandaController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\CajaController;

/*
|--------------------------------------------------------------------------
| MOZO
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Mozo\DashboardController as MozoDashboardController;
use App\Http\Controllers\Mozo\MesaController as MozoMesaController;
use App\Http\Controllers\Mozo\ComandaController as MozoComandaController;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (($user->role ?? null) === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('mozo.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::get('/carta', [CartaController::class, 'index'])->name('carta.index');

        Route::post('/carta/categorias', [CartaController::class, 'storeCategoria'])->name('carta.categorias.store');
        Route::put('/carta/categorias/{categoria}', [CartaController::class, 'updateCategoria'])->name('carta.categorias.update');
        Route::delete('/carta/categorias/{categoria}', [CartaController::class, 'destroyCategoria'])->name('carta.categorias.destroy');
        Route::patch('/carta/categorias/{categoria}/toggle', [CartaController::class, 'toggleCategoria'])->name('carta.categorias.toggle');

        Route::post('/carta/items', [CartaController::class, 'storeItem'])->name('carta.items.store');
        Route::put('/carta/items/{item}', [CartaController::class, 'updateItem'])->name('carta.items.update');
        Route::delete('/carta/items/{item}', [CartaController::class, 'destroyItem'])->name('carta.items.destroy');
        Route::patch('/carta/items/{item}/toggle', [CartaController::class, 'toggleItem'])->name('carta.items.toggle');

        Route::get('/mesas', [AdminMesaController::class, 'index'])->name('mesas.index');
        Route::post('/mesas', [AdminMesaController::class, 'store'])->name('mesas.store');
        Route::put('/mesas/{mesa}', [AdminMesaController::class, 'update'])->name('mesas.update');
        Route::delete('/mesas/{mesa}', [AdminMesaController::class, 'destroy'])->name('mesas.destroy');
        Route::post('/mesas/{mesa}/estado', [AdminMesaController::class, 'setEstado'])->name('mesas.estado');
        Route::post('/mesas/{mesa}/liberar', [AdminMesaController::class, 'liberar'])->name('mesas.liberar');

        Route::get('/comandas', [AdminComandaController::class, 'index'])->name('comandas.index');
        Route::get('/comandas/{comanda}', [AdminComandaController::class, 'show'])->name('comandas.show');
        Route::post('/comandas/{comanda}/cobrar', [AdminComandaController::class, 'cobrar'])->name('comandas.cobrar');

        Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
        Route::get('/caja/pendientes', [CajaController::class, 'pendientes'])->name('caja.pendientes');
        Route::get('/caja/comandas/{comanda}', [CajaController::class, 'show'])->name('caja.show');
        Route::get('/caja/comandas/{comanda}/cuenta', [CajaController::class, 'cuenta'])->name('caja.cuenta');
        Route::post('/caja/comandas/{comanda}/cobrar', [CajaController::class, 'cobrar'])->name('caja.cobrar');

        Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    });

    /*
    |--------------------------------------------------------------------------
    | MOZO (y admin si usa POS)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:mozo,admin'])
        ->prefix('mozo')
        ->name('mozo.')
        ->group(function () {

        Route::get('/', [MozoDashboardController::class, 'index'])->name('dashboard');

        // Partials ajax
        Route::get('/dashboard/mesas', [MozoDashboardController::class, 'partialMesas'])->name('dashboard.mesas');
        Route::get('/dashboard/comanda', [MozoDashboardController::class, 'partialComanda'])->name('dashboard.comanda');
        Route::get('/dashboard/cuenta', [MozoDashboardController::class, 'partialCuenta'])->name('dashboard.cuenta');

        // Mesas (mozo)
        Route::post('/mesas/{mesa}/ocupar',  [MozoMesaController::class, 'ocupar'])->name('mesas.ocupar');
        Route::post('/mesas/{mesa}/liberar', [MozoMesaController::class, 'liberar'])->name('mesas.liberar');

        // ✅ NUEVO: agregar items por mesa (crea comanda si no hay)
        Route::post('/mesas/{mesa}/items', [MozoComandaController::class, 'addItemsForMesa'])
            ->name('mesas.items.add');

        // Compat: crear comanda manual (si lo seguís usando en algún lugar)
        Route::post('/mesas/{mesa}/comandas', [MozoComandaController::class, 'createForMesa'])
            ->name('comandas.createForMesa');

        // Agregar items a comanda existente
        Route::post('/comandas/{comanda}/items', [MozoComandaController::class, 'addItem'])
            ->name('comandas.items.add');

        Route::patch('/comanda-items/{comandaItem}', [MozoComandaController::class, 'updateItem'])
            ->name('comandas.items.update');

        // ✅ AHORA BORRA (no anula)
        Route::delete('/comanda-items/{comandaItem}', [MozoComandaController::class, 'removeItem'])
            ->name('comandas.items.delete');

        Route::patch('/comandas/{comanda}/estado', [MozoComandaController::class, 'setEstado'])
            ->name('comandas.estado');

        Route::post('/comandas/{comanda}/solicitar-cuenta', [MozoComandaController::class, 'solicitarCuenta'])
            ->name('comandas.solicitarCuenta');
    });
});
