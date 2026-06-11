<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class RegistroVerificacionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\RegistroVerificacion::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/registro-verificacion');
        CRUD::setEntityNameStrings('registro de verificación', 'registros de verificación');
    }

    protected function setupListOperation(): void
    {
        CRUD::orderBy('created_at', 'desc');

        CRUD::column('id');
        CRUD::addColumn([
            'name' => 'usuaria_id',
            'label' => 'Usuaria',
            'type' => 'closure',
            'function' => fn ($entry) => $entry->usuaria?->name.' ('.$entry->usuaria?->email.')',
        ]);
        CRUD::addColumn([
            'name' => 'estado',
            'label' => 'Estado',
            'type' => 'closure',
            'function' => fn ($entry) => str_replace('_', ' ', $entry->estado),
        ]);
        CRUD::column('fecha_inicio')->label('Inicio')->type('datetime');
        CRUD::column('fecha_resolucion')->label('Resolución')->type('datetime');

        // Filtros vía query string (ver nota en UsuariaCrudController).
        if (request()->filled('estado')) {
            CRUD::addClause('where', 'estado', request('estado'));
        }

        if (request()->filled('desde')) {
            CRUD::addClause('where', 'fecha_inicio', '>=', request('desde'));
        }

        if (request()->filled('hasta')) {
            CRUD::addClause('where', 'fecha_inicio', '<=', request('hasta').' 23:59:59');
        }

        CRUD::addButtonFromView('top', 'filtros_registro', 'filtros_registro', 'end');

        // Botón por fila hacia la página de revisión detallada.
        CRUD::addButtonFromView('line', 'revisar', 'revisar', 'beginning');
    }
}
