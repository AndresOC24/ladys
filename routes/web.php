<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
| La raíz dirige al login; login y registro viven en routes/auth.php.
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Flujo de verificación (auth, SIN exigir verificación aprobada)
|--------------------------------------------------------------------------
| Aquí vive el proceso de validación de identidad. Las fases siguientes
| agregan la carga de cédula, selfie y resultado.
*/

Route::middleware('auth')->prefix('verificacion')->name('verificacion.')->group(function () {
    Route::get('/estado', [VerificacionController::class, 'estado'])->name('estado');
    Route::get('/estado-json', [VerificacionController::class, 'estadoJson'])->name('estado-json');

    Route::get('/paso-1', [VerificacionController::class, 'mostrarPaso1'])->name('paso1');
    Route::post('/paso-1', [VerificacionController::class, 'guardarPaso1'])->name('paso1.guardar');
    Route::get('/paso-2', [VerificacionController::class, 'mostrarPaso2'])->name('paso2');
    Route::post('/paso-2', [VerificacionController::class, 'guardarPaso2'])->name('paso2.guardar');
    Route::get('/paso-3', [VerificacionController::class, 'mostrarPaso3'])->name('paso3');
    Route::post('/paso-3', [VerificacionController::class, 'guardarPaso3'])->name('paso3.guardar');

    Route::get('/procesando', [VerificacionController::class, 'procesando'])->name('procesando');
    Route::get('/resultado', [VerificacionController::class, 'mostrarResultado'])->name('resultado');
});

/*
|--------------------------------------------------------------------------
| Rutas de usuaria verificada (auth + verificada)
|--------------------------------------------------------------------------
| Solo accesibles con estado_verificacion = 'aprobada'.
*/

Route::middleware(['auth', 'verificada'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin: el grupo /admin lo maneja Backpack (routes/backpack/custom.php)
| con el middleware CheckIfAdmin que exige rol administrador.
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
