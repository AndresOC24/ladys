<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente HTTP del servicio de IA (FastAPI).
 *
 * Contrato de retorno de cada método:
 *  - ['exito' => true,  'datos' => array]                      → respuesta 2xx
 *  - ['exito' => false, 'error' => string, 'status' => int]    → 4xx (imagen inválida / sin rostro)
 *  - lanza ConnectionException / RuntimeException               → caída o 5xx (el Job reintenta)
 */
class ServicioIA
{
    public function verificarRostro(string $rutaSelfie, string $rutaCedula, ?float $umbral = null): array
    {
        // Se adjuntan los contenidos (no handles de fopen) para no dejar
        // archivos bloqueados en Windows.
        $peticion = $this->cliente()
            ->attach('selfie', file_get_contents($rutaSelfie), basename($rutaSelfie))
            ->attach('cedula', file_get_contents($rutaCedula), basename($rutaCedula));

        return $this->interpretar(
            $peticion->post('/verificar-rostro', $umbral !== null ? ['umbral' => $umbral] : [])
        );
    }

    public function detectarLiveness(string $rutaSelfie, ?float $umbral = null): array
    {
        $peticion = $this->cliente()
            ->attach('selfie', file_get_contents($rutaSelfie), basename($rutaSelfie));

        return $this->interpretar(
            $peticion->post('/detectar-liveness', $umbral !== null ? ['umbral' => $umbral] : [])
        );
    }

    public function extraerDatosCedula(string $rutaCedula, ?string $rutaReverso = null): array
    {
        $peticion = $this->cliente()
            ->attach('cedula', file_get_contents($rutaCedula), basename($rutaCedula));

        if ($rutaReverso !== null) {
            $peticion->attach('reverso', file_get_contents($rutaReverso), basename($rutaReverso));
        }

        return $this->interpretar($peticion->post('/ocr-cedula'));
    }

    /**
     * Recorte JPEG del rostro impreso en el documento, o null si el servicio
     * no pudo extraerlo (es un dato complementario, no bloquea el flujo).
     */
    public function extraerRostroCedula(string $rutaCedula): ?string
    {
        $respuesta = $this->cliente()
            ->attach('cedula', file_get_contents($rutaCedula), basename($rutaCedula))
            ->post('/extraer-rostro');

        return $respuesta->successful() ? $respuesta->body() : null;
    }

    protected function cliente(): PendingRequest
    {
        return Http::baseUrl(config('ia.url'))
            ->timeout(config('ia.timeout'))
            ->retry(config('ia.reintentos_conexion'), 1000, throw: false)
            ->acceptJson();
    }

    protected function interpretar(Response $respuesta): array
    {
        if ($respuesta->successful()) {
            return ['exito' => true, 'datos' => $respuesta->json()];
        }

        // 4xx: problema con la imagen enviada (corrupta o sin rostro);
        // es un resultado de negocio, no un fallo del servicio.
        if ($respuesta->clientError()) {
            return [
                'exito' => false,
                'error' => $respuesta->json('detail') ?? 'La imagen no pudo ser procesada.',
                'status' => $respuesta->status(),
            ];
        }

        // 5xx: fallo interno del servicio de IA → el Job debe reintentar.
        throw new RuntimeException(
            "El servicio de IA respondió {$respuesta->status()}: ".($respuesta->json('detail') ?? $respuesta->body())
        );
    }
}
