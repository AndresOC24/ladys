<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RolRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class RolCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\Rol::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/rol');
        CRUD::setEntityNameStrings('rol', 'roles');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('nombre')->label('Nombre');
        CRUD::column('descripcion')->label('Descripción');
        CRUD::addColumn([
            'name' => 'usuarias_count',
            'label' => 'Usuarias',
            'type' => 'closure',
            'function' => fn ($entry) => $entry->usuarias()->count(),
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(RolRequest::class);

        CRUD::field('nombre')->label('Nombre')->hint('Los roles pasajera, conductora y administrador son usados por el sistema: no renombrar.');
        CRUD::field('descripcion')->label('Descripción');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
