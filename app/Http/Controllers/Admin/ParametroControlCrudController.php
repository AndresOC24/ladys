<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ParametroControlRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ParametroControlCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\ParametroControl::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/parametro-control');
        CRUD::setEntityNameStrings('parámetro de control', 'parámetros de control');

        // Los parámetros gobiernan la decisión automática: no se eliminan,
        // se desactivan con el campo "activo".
    }

    protected function setupListOperation(): void
    {
        CRUD::column('categoria')->label('Categoría');
        CRUD::column('clave')->label('Clave');
        CRUD::column('valor')->label('Valor');
        CRUD::column('descripcion')->label('Descripción')->limit(80);
        CRUD::column('activo')->label('Activo')->type('boolean');

        // Filtro vía query string (ver nota en UsuariaCrudController).
        if (request()->filled('categoria')) {
            CRUD::addClause('where', 'categoria', request('categoria'));
        }
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ParametroControlRequest::class);

        CRUD::field('categoria')->label('Categoría')->hint('Ej.: biometrico, documento');
        CRUD::field('clave')->label('Clave')->hint('Identificador único usado por el sistema. No cambiar sin revisar el código.');
        CRUD::field('valor')->label('Valor')->hint('Umbrales: número decimal (ej. 0.68). Reglas: expresión regular o número entero.');
        CRUD::field('descripcion')->label('Descripción');
        CRUD::field('activo')->label('Activo')->type('checkbox')->default(true);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
        CRUD::field('clave')->attributes(['readonly' => 'readonly']);
    }
}
