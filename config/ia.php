<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicio de IA (FastAPI)
    |--------------------------------------------------------------------------
    | Conexión al servicio Python que ejecuta el reconocimiento facial,
    | la detección de vida y el OCR. Solo accesible en red interna.
    */

    'url' => env('IA_SERVICE_URL', 'http://127.0.0.1:8001'),

    // La primera inferencia tras el arranque del servicio tarda ~20s
    // (carga de modelos); el timeout debe contemplarlo.
    'timeout' => (int) env('IA_SERVICE_TIMEOUT', 120),

    'reintentos_conexion' => (int) env('IA_SERVICE_RETRIES', 1),

];
