<?php

namespace App\Services;

use App\Models\ParametroControl;
use Carbon\Carbon;
use Throwable;

/**
 * Valida la estructura y consistencia de los datos extraídos de la cédula
 * por OCR, aplicando los parámetros de control (categoría "documento").
 *
 * Reglas:
 *  1. Presencia de los campos obligatorios (parámetro campos_obligatorios).
 *  2. Número de cédula conforme a la expresión regular (regex_cedula).
 *  3. Nombre completo con longitud mínima (longitud_min_nombre).
 *  4. Coherencia: la fecha de nacimiento debe ser anterior a la de emisión.
 *  5. Vigencia: la fecha de vencimiento debe ser posterior a hoy.
 */
class ValidadorEstructuralCedula
{
    protected const ETIQUETAS = [
        'numero_cedula' => 'número de cédula',
        'nombre_completo' => 'nombre completo',
        'fecha_nacimiento' => 'fecha de nacimiento',
        'fecha_emision' => 'fecha de emisión',
        'fecha_vencimiento' => 'fecha de vencimiento',
        'lugar_nacimiento' => 'lugar de nacimiento',
    ];

    /**
     * @param  array  $datos  Campos extraídos por el OCR (numero_cedula, nombre_completo, fechas...)
     * @return array{valido: bool, errores: array<int, string>}
     */
    public function validar(array $datos): array
    {
        $errores = [
            ...$this->validarPresencia($datos),
            ...$this->validarNumeroCedula($datos['numero_cedula'] ?? null),
            ...$this->validarNombre($datos['nombre_completo'] ?? null),
            ...$this->validarFechas($datos),
        ];

        return [
            'valido' => $errores === [],
            'errores' => array_values($errores),
        ];
    }

    /** @return array<int, string> */
    protected function validarPresencia(array $datos): array
    {
        $obligatorios = array_filter(array_map('trim', explode(',', ParametroControl::valorDe(
            'campos_obligatorios',
            'numero_cedula,nombre_completo,fecha_nacimiento,fecha_emision,fecha_vencimiento'
        ))));

        $faltantes = array_filter(
            $obligatorios,
            fn (string $campo) => blank($datos[$campo] ?? null)
        );

        if ($faltantes === []) {
            return [];
        }

        $etiquetas = array_map(fn ($campo) => self::ETIQUETAS[$campo] ?? $campo, $faltantes);

        return ['No se pudo leer del documento: '.implode(', ', $etiquetas).'.'];
    }

    /** @return array<int, string> */
    protected function validarNumeroCedula(?string $numero): array
    {
        if (blank($numero)) {
            return []; // la ausencia ya la reporta la regla de presencia
        }

        $regex = ParametroControl::valorDe('regex_cedula', '^[0-9]{7,8}$');

        if (! preg_match('/'.$regex.'/', $numero)) {
            return ["El número de cédula \"{$numero}\" no cumple el formato esperado del documento boliviano."];
        }

        return [];
    }

    /** @return array<int, string> */
    protected function validarNombre(?string $nombre): array
    {
        if (blank($nombre)) {
            return [];
        }

        $minimo = (int) ParametroControl::valorDe('longitud_min_nombre', '3');

        if (mb_strlen(trim($nombre)) < $minimo) {
            return ["El nombre leído (\"{$nombre}\") es demasiado corto para ser válido (mínimo {$minimo} caracteres)."];
        }

        return [];
    }

    /** @return array<int, string> */
    protected function validarFechas(array $datos): array
    {
        $errores = [];

        $nacimiento = $this->parsear($datos['fecha_nacimiento'] ?? null);
        $emision = $this->parsear($datos['fecha_emision'] ?? null);
        $vencimiento = $this->parsear($datos['fecha_vencimiento'] ?? null);

        if ($nacimiento && $emision && $nacimiento->greaterThanOrEqualTo($emision)) {
            $errores[] = sprintf(
                'Fechas incoherentes: la fecha de nacimiento (%s) debería ser anterior a la de emisión (%s).',
                $nacimiento->format('d/m/Y'),
                $emision->format('d/m/Y')
            );
        }

        if ($vencimiento && $vencimiento->lessThanOrEqualTo(today())) {
            $errores[] = sprintf('El documento está vencido (expiró el %s).', $vencimiento->format('d/m/Y'));
        }

        return $errores;
    }

    protected function parsear(mixed $fecha): ?Carbon
    {
        if (blank($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha);
        } catch (Throwable) {
            return null;
        }
    }
}
