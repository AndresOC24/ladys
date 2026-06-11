<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UsuariaRequest;
use App\Models\Rol;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class UsuariaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\Usuaria::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/usuaria');
        CRUD::setEntityNameStrings('usuaria', 'usuarias');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('name')->label('Nombre');
        CRUD::column('email')->label('Correo');
        CRUD::addColumn([
            'name' => 'rol_id',
            'label' => 'Rol',
            'type' => 'closure',
            'function' => fn ($entry) => ucfirst($entry->rol?->nombre ?? '—'),
        ]);
        CRUD::column('telefono')->label('Teléfono');
        CRUD::addColumn([
            'name' => 'estado_verificacion',
            'label' => 'Estado',
            'type' => 'closure',
            'function' => fn ($entry) => str_replace('_', ' ', $entry->estado_verificacion),
        ]);
        CRUD::column('created_at')->label('Registro')->type('datetime');

        // Filtros vía query string: el datatable reenvía los parámetros de la
        // página al endpoint /search, por lo que las cláusulas aplican también
        // en las peticiones ajax. (Los filter() nativos son de Backpack PRO.)
        if (request()->filled('rol')) {
            CRUD::addClause('where', 'rol_id', request('rol'));
        }

        if (request()->filled('estado')) {
            CRUD::addClause('where', 'estado_verificacion', request('estado'));
        }

        CRUD::addButtonFromView('top', 'filtros_usuaria', 'filtros_usuaria', 'end');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(UsuariaRequest::class);

        CRUD::field('name')->label('Nombre completo');
        CRUD::field('email')->label('Correo')->type('email');
        CRUD::field('password')->label('Contraseña')->type('password');
        CRUD::addField([
            'name' => 'rol_id',
            'label' => 'Rol',
            'type' => 'select_from_array',
            'options' => Rol::pluck('nombre', 'id')->map(fn ($n) => ucfirst($n))->toArray(),
            'hint' => 'Selecciona "Administrador" para crear una cuenta de administración.',
        ]);
        CRUD::field('telefono')->label('Teléfono');
        CRUD::field('fecha_nacimiento')->label('Fecha de nacimiento')->type('date');
        CRUD::addField([
            'name' => 'estado_verificacion',
            'label' => 'Estado de verificación',
            'type' => 'select_from_array',
            'options' => [
                'pendiente' => 'Pendiente',
                'en_proceso' => 'En proceso',
                'aprobada' => 'Aprobada',
                'rechazada' => 'Rechazada',
                'en_revision' => 'En revisión',
            ],
            'default' => 'pendiente',
            'hint' => 'Las cuentas de administración deben quedar en "Aprobada".',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
        CRUD::field('password')->hint('Dejar vacío para mantener la contraseña actual.');
    }

    public function store()
    {
        $this->limpiarPasswordVacio();

        return $this->traitStore();
    }

    public function update()
    {
        $this->limpiarPasswordVacio();

        return $this->traitUpdate();
    }

    /**
     * El cast "hashed" del modelo se encarga del hash; aquí solo se evita
     * sobreescribir la contraseña cuando el campo llega vacío en updates.
     */
    protected function limpiarPasswordVacio(): void
    {
        if (! request()->filled('password')) {
            $this->crud->getRequest()->request->remove('password');
            request()->request->remove('password');
        }
    }
}
