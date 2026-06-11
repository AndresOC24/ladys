<?php

namespace App\Services;

use App\Models\ParametroControl;

/**
 * Aplica las reglas de decisión de la validación administrativa híbrida.
 *
 * Reglas (umbrales leídos de parametros_control, nunca hardcodeados):
 *  1. Liveness falla (spoof o sin rostro)                  → rechazada
 *  2. Distancia facial > umbral_dudoso                     → rechazada
 *  3. umbral_aprobado < distancia <= umbral_dudoso         → en_revision
 *  4. OCR sin campos mínimos (cédula válida + nombre)      → en_revision
 *  5. Todo correcto                                        → aprobada
 */
class EvaluadorVerificacion
{
    /**
     * @param  array  $liveness  Respuesta de ServicioIA::detectarLiveness
     * @param  array  $facial  Respuesta de ServicioIA::verificarRostro
     * @param  array  $ocr  Respuesta de ServicioIA::extraerDatosCedula
     * @return array{veredicto: string, componentes: array<string, array>}
     */
    public function evaluar(array $liveness, array $facial, array $ocr): array
    {
        $componentes = [
            'liveness' => $this->evaluarLiveness($liveness),
            'facial' => $this->evaluarFacial($facial),
            'ocr' => $this->evaluarOcr($ocr),
        ];

        return [
            'veredicto' => $this->veredicto($componentes),
            'componentes' => $componentes,
        ];
    }

    protected function evaluarLiveness(array $respuesta): array
    {
        if (! $respuesta['exito']) {
            return $this->componenteFallido($respuesta, 'rechazado');
        }

        $datos = $respuesta['datos'];

        return [
            'resultado' => $datos['es_real'] ? 'aprobado' : 'rechazado',
            'puntaje' => $datos['score'],
            'detalles' => [
                'umbral_aplicado' => $datos['umbral_aplicado'],
                'motivo' => $datos['es_real'] ? null : 'La captura no superó la prueba de vida (posible intento de suplantación).',
            ],
        ];
    }

    protected function evaluarFacial(array $respuesta): array
    {
        if (! $respuesta['exito']) {
            return $this->componenteFallido($respuesta, 'rechazado');
        }

        $datos = $respuesta['datos'];
        $distancia = $datos['distancia'];
        $umbralAprobado = (float) ParametroControl::valorDe('umbral_facial_aprobado', '0.68');
        $umbralDudoso = (float) ParametroControl::valorDe('umbral_facial_dudoso', '0.75');

        $resultado = match (true) {
            $distancia <= $umbralAprobado => 'aprobado',
            $distancia <= $umbralDudoso => 'dudoso',
            default => 'rechazado',
        };

        return [
            'resultado' => $resultado,
            'puntaje' => $distancia,
            'detalles' => [
                'umbral_aprobado' => $umbralAprobado,
                'umbral_dudoso' => $umbralDudoso,
                'modelo' => $datos['modelo'] ?? null,
                'motivo' => match ($resultado) {
                    'dudoso' => 'La similitud facial quedó en zona dudosa; requiere revisión humana.',
                    'rechazado' => 'El rostro capturado no coincide con la fotografía del documento.',
                    default => null,
                },
            ],
        ];
    }

    protected function evaluarOcr(array $respuesta): array
    {
        if (! $respuesta['exito']) {
            // Un documento ilegible no es fraude comprobado: lo revisa una persona.
            return $this->componenteFallido($respuesta, 'dudoso');
        }

        $datos = $respuesta['datos'];
        $regexCedula = ParametroControl::valorDe('regex_cedula', '^[0-9]{7,8}$');
        $longitudMinima = (int) ParametroControl::valorDe('longitud_min_nombre', '3');

        $faltantes = [];

        $numero = $datos['numero_cedula'] ?? null;
        if (! $numero || ! preg_match('/'.$regexCedula.'/', $numero)) {
            $faltantes[] = 'número de cédula';
        }

        $nombre = trim($datos['nombre_completo'] ?? '');
        if (mb_strlen($nombre) < $longitudMinima) {
            $faltantes[] = 'nombre completo';
        }

        return [
            'resultado' => $faltantes === [] ? 'aprobado' : 'dudoso',
            'puntaje' => $datos['confianza_promedio'] ?? null,
            'detalles' => [
                'campos_detectados' => $datos['campos_detectados'] ?? 0,
                'motivo' => $faltantes === []
                    ? null
                    : 'El OCR no pudo validar: '.implode(', ', $faltantes).'. Requiere revisión humana.',
            ],
        ];
    }

    protected function veredicto(array $componentes): string
    {
        $resultados = array_column($componentes, 'resultado');

        if (in_array('rechazado', $resultados, true)) {
            return 'rechazada';
        }

        if (in_array('dudoso', $resultados, true)) {
            return 'en_revision';
        }

        return 'aprobada';
    }

    protected function componenteFallido(array $respuesta, string $resultado): array
    {
        return [
            'resultado' => $resultado,
            'puntaje' => null,
            'detalles' => [
                'motivo' => $respuesta['error'],
                'http_status' => $respuesta['status'],
            ],
        ];
    }
}
