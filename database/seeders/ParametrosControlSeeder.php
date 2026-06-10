<?php

namespace Database\Seeders;

use App\Models\ParametroControl;
use Illuminate\Database\Seeder;

class ParametrosControlSeeder extends Seeder
{
    public function run(): void
    {
        $parametros = [
            [
                'categoria' => 'biometrico',
                'clave' => 'umbral_facial_aprobado',
                'valor' => '0.68',
                'descripcion' => 'Distancia coseno máxima (ArcFace) para aprobar automáticamente la verificación facial 1:1',
            ],
            [
                'categoria' => 'biometrico',
                'clave' => 'umbral_facial_dudoso',
                'valor' => '0.75',
                'descripcion' => 'Distancia coseno máxima para marcar como dudoso y derivar a revisión administrativa; por encima se rechaza',
            ],
            [
                'categoria' => 'biometrico',
                'clave' => 'umbral_liveness',
                'valor' => '0.85',
                'descripcion' => 'Puntaje mínimo de MiniFASNet para considerar que la imagen proviene de una persona real',
            ],
            [
                'categoria' => 'documento',
                'clave' => 'regex_cedula',
                'valor' => '^[0-9]{7,8}$',
                'descripcion' => 'Expresión regular que debe cumplir el número de cédula de identidad boliviana',
            ],
            [
                'categoria' => 'documento',
                'clave' => 'longitud_min_nombre',
                'valor' => '3',
                'descripcion' => 'Longitud mínima aceptada para el nombre completo extraído por OCR',
            ],
        ];

        foreach ($parametros as $parametro) {
            ParametroControl::updateOrCreate(['clave' => $parametro['clave']], $parametro);
        }
    }
}
