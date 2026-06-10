<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
| Landing, login y registro (estas dos últimas en routes/auth.php).
*/

Route::get('/', function () {
    return view('welcome');
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
