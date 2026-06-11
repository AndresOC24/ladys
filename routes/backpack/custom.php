<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('usuaria', 'UsuariaCrudController');
    Route::crud('registro-verificacion', 'RegistroVerificacionCrudController');
    Route::crud('parametro-control', 'ParametroControlCrudController');
    Route::crud('rol', 'RolCrudController');

    Route::get('revision/{registro}', 'RevisionAdminController@show')->name('admin.revision.show');
    Route::post('revision/{registro}', 'RevisionAdminController@decidir')->name('admin.revision.decidir');

    Route::get('documento/{documento}', 'DocumentoAdminController@show')->name('admin.documento.show');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
