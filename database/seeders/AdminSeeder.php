<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuaria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdministrador = Rol::where('nombre', 'administrador')->first();

        Usuaria::updateOrCreate(
            ['email' => 'admin@ladysongo.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin1234'),
                'rol_id' => $rolAdministrador?->id,
                'estado_verificacion' => 'aprobada',
                'email_verified_at' => now(),
            ]
        );
    }
}
