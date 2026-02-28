<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
use App\Http\Controllers\Admin\CajaHistorialController;
use App\Http\Controllers\Admin\CajaTurnoController;
use App\Http\Controllers\Admin\CajaMovimientoController;
use App\Http\Controllers\Admin\MozosController;

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

    /*
    |--------------------------------------------------------------------------
    | Dashboard dispatcher (Breeze)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // ✅ Safety extra: si es mozo inactivo, lo sacamos (por las dudas)
        if (($user->role ?? null) === 'mozo' && (string)($user->estado ?? 'activo') !== 'activo') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu usuario está inactivo. Pedí al administrador que te habilite.',
            ]);
        }

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

            /*
            |--------------------------------------------------------------------------
            | CARTA
            |--------------------------------------------------------------------------
            */
            Route::get('/carta', [CartaController::class, 'index'])->name('carta.index');

            Route::post('/carta/categorias', [CartaController::class, 'storeCategoria'])->name('carta.categorias.store');
            Route::put('/carta/categorias/{categoria}', [CartaController::class, 'updateCategoria'])->name('carta.categorias.update');
            Route::delete('/carta/categorias/{categoria}', [CartaController::class, 'destroyCategoria'])->name('carta.categorias.destroy');
            Route::patch('/carta/categorias/{categoria}/toggle', [CartaController::class, 'toggleCategoria'])->name('carta.categorias.toggle');

            Route::post('/carta/items', [CartaController::class, 'storeItem'])->name('carta.items.store');
            Route::put('/carta/items/{item}', [CartaController::class, 'updateItem'])->name('carta.items.update');
            Route::delete('/carta/items/{item}', [CartaController::class, 'destroyItem'])->name('carta.items.destroy');
            Route::patch('/carta/items/{item}/toggle', [CartaController::class, 'toggleItem'])->name('carta.items.toggle');

            /*
            |--------------------------------------------------------------------------
            | MESAS (ADMIN)
            |--------------------------------------------------------------------------
            */
            Route::get('/mesas', [AdminMesaController::class, 'index'])->name('mesas.index');
            Route::post('/mesas', [AdminMesaController::class, 'store'])->name('mesas.store');
            Route::put('/mesas/{mesa}', [AdminMesaController::class, 'update'])->name('mesas.update');
            Route::delete('/mesas/{mesa}', [AdminMesaController::class, 'destroy'])->name('mesas.destroy');
            Route::patch('/mesas/{mesa}/estado', [AdminMesaController::class, 'setEstado'])->name('mesas.estado');
            Route::patch('/mesas/{mesa}/liberar', [AdminMesaController::class, 'liberar'])->name('mesas.liberar');

            /*
            |--------------------------------------------------------------------------
            | COMANDAS (ADMIN)
            |--------------------------------------------------------------------------
            */
            Route::get('/comandas', [AdminComandaController::class, 'index'])->name('comandas.index');

            // ✅ Poll AJAX (refresco automático)
            Route::get('/comandas/poll', [AdminComandaController::class, 'poll'])->name('comandas.poll');

            // ✅ Imprimir comanda (ticket cocina)
            Route::get('/comandas/{comanda}/print', [AdminComandaController::class, 'print'])->name('comandas.print');

            Route::get('/comandas/{comanda}', [AdminComandaController::class, 'show'])->name('comandas.show');

            /*
            |--------------------------------------------------------------------------
            | CAJA (operación diaria)
            |--------------------------------------------------------------------------
            */
            Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
            Route::get('/caja/pendientes', [CajaController::class, 'pendientes'])->name('caja.pendientes');

            Route::get('/caja/pendientes-poll', [CajaController::class, 'pendientesPoll'])->name('caja.pendientesPoll');

            Route::get('/caja/comandas/{comanda}', [CajaController::class, 'show'])->name('caja.show');
            Route::get('/caja/comandas/{comanda}/cuenta', [CajaController::class, 'cuenta'])->name('caja.cuenta');
            Route::post('/caja/comandas/{comanda}/cobrar', [CajaController::class, 'cobrar'])->name('caja.cobrar');

            // Admin puede agregar / quitar items con cuenta solicitada
            Route::post('/caja/comandas/{comanda}/items', [CajaController::class, 'addItems'])
                ->name('caja.items.add');

            Route::delete('/caja/comanda-items/{comandaItem}', [CajaController::class, 'deleteItem'])
                ->name('caja.items.delete');

            /*
            |--------------------------------------------------------------------------
            | CAJA TURNO (abrir / cerrar)
            |--------------------------------------------------------------------------
            */
            Route::post('/caja/turno/abrir', [CajaTurnoController::class, 'abrir'])
                ->name('caja.turno.abrir');

            Route::post('/caja/turno/cerrar', [CajaTurnoController::class, 'cerrar'])
                ->name('caja.turno.cerrar');

            /*
            |--------------------------------------------------------------------------
            | MOVIMIENTOS DE CAJA (ingresos / egresos)
            |--------------------------------------------------------------------------
            */
            Route::post('/caja/movimientos', [CajaMovimientoController::class, 'store'])
                ->name('caja.movimientos.store');

            // ✅ Alias para las vistas (NO tocar blade)
            Route::post('/caja/turno/movimiento', [CajaMovimientoController::class, 'store'])
                ->name('caja.turno.movimiento');

            Route::delete('/caja/movimientos/{movimiento}', [CajaMovimientoController::class, 'destroy'])
                ->name('caja.movimientos.destroy');

            /*
            |--------------------------------------------------------------------------
            | MOZOS (admin)
            |--------------------------------------------------------------------------
            */
            Route::get('/caja/mozos', [MozosController::class, 'index'])
                ->name('caja.mozos.index');

            Route::patch('/caja/mozos/{user}/estado', [MozosController::class, 'setEstado'])
                ->name('caja.mozos.estado');

            Route::patch('/caja/mozos/{user}/nombre', [MozosController::class, 'updateNombre'])
                ->name('caja.mozos.nombre');

            /*
            |--------------------------------------------------------------------------
            | HISTORIAL DE CAJAS
            |--------------------------------------------------------------------------
            */
            Route::get('/historial', [CajaHistorialController::class, 'index'])
                ->name('caja.historial.index');

            /*
            |--------------------------------------------------------------------------
            | VENTAS
            |--------------------------------------------------------------------------
            */
            Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])
                ->name('ventas.ticket');
        });

    /*
    |--------------------------------------------------------------------------
    | MOZO (y admin usando POS)
    | ✅ Agregado: mozo.activo
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:mozo,admin', 'mozo.activo'])
        ->prefix('mozo')
        ->name('mozo.')
        ->group(function () {

            Route::get('/', [MozoDashboardController::class, 'index'])->name('dashboard');

            // Partials AJAX
            Route::get('/dashboard/mesas', [MozoDashboardController::class, 'partialMesas'])->name('dashboard.mesas');
            Route::get('/dashboard/comanda', [MozoDashboardController::class, 'partialComanda'])->name('dashboard.comanda');
            Route::get('/dashboard/cuenta', [MozoDashboardController::class, 'partialCuenta'])->name('dashboard.cuenta');

            // Mesas
            Route::post('/mesas/{mesa}/ocupar', [MozoMesaController::class, 'ocupar'])->name('mesas.ocupar');

            // Comandas
            Route::post('/mesas/{mesa}/items', [MozoComandaController::class, 'addItemsForMesa'])
                ->name('mesas.items.add');

            Route::post('/mesas/{mesa}/comandas', [MozoComandaController::class, 'createForMesa'])
                ->name('comandas.createForMesa');

            Route::post('/comandas/{comanda}/items', [MozoComandaController::class, 'addItem'])
                ->name('comandas.items.add');

            Route::patch('/comanda-items/{comandaItem}', [MozoComandaController::class, 'updateItem'])
                ->name('comandas.items.update');

            Route::patch('/comandas/{comanda}/estado', [MozoComandaController::class, 'setEstado'])
                ->name('comandas.estado');

            Route::post('/comandas/{comanda}/solicitar-cuenta', [MozoComandaController::class, 'solicitarCuenta'])
                ->name('comandas.solicitarCuenta');
        });
});