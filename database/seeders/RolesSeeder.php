<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'pasajera', 'descripcion' => 'Usuaria que solicita viajes en el servicio de transporte'],
            ['nombre' => 'conductora', 'descripcion' => 'Usuaria que conduce un vehículo dentro del servicio'],
            ['nombre' => 'administrador', 'descripcion' => 'Usuario interno que gestiona revisiones administrativas y el sistema'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
