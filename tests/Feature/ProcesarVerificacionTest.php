<?php

use App\Jobs\ProcesarVerificacion;
use App\Models\RegistroVerificacion;
use App\Models\Usuaria;
use Database\Seeders\ParametrosControlSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    $this->seed(ParametrosControlSeeder::class);

    $this->usuaria = Usuaria::factory()->create(['estado_verificacion' => 'en_proceso']);
    $this->registro = $this->usuaria->registrosVerificacion()->create([
        'fecha_inicio' => now(),
        'estado' => 'en_proceso',
    ]);

    foreach (['selfie' => 'selfies', 'anverso' => 'cedulas', 'reverso' => 'cedulas'] as $tipo => $carpeta) {
        $ruta = "{$carpeta}/{$this->registro->id}_{$tipo}_1.jpg";
        Storage::disk('private')->put($ruta, 'contenido-imagen');
        $this->registro->documentos()->create([
            'tipo' => $tipo,
            'ruta_archivo' => $ruta,
            'hash_archivo' => hash('sha256', 'contenido-imagen'),
        ]);
    }
});

function fakeIa(array $liveness = [], array $facial = [], array $ocr = []): void
{
    Http::fake([
        '*/detectar-liveness' => Http::response($liveness + [
            'es_real' => true, 'score' => 0.95, 'umbral_aplicado' => 0.85, 'tiempo_ms' => 300,
        ]),
        '*/verificar-rostro' => Http::response($facial + [
            'distancia' => 0.51, 'umbral_aplicado' => 0.68, 'coincide' => true, 'modelo' => 'ArcFace', 'tiempo_ms' => 1200,
        ]),
        '*/ocr-cedula' => Http::response($ocr + [
            'numero_cedula' => '1234567',
            'serie' => '21333',
            'seccion' => '11222',
            'nombre_completo' => 'MARIA RENEE RODRIGUEZ GONZALEZ',
            'fecha_nacimiento' => '2003-04-05',
            'fecha_emision' => '2023-11-01',
            'fecha_vencimiento' => '2028-11-01',
            'lugar_nacimiento' => 'SANTA CRUZ DE LA SIERRA',
            'domicilio' => 'C. LAS PALMERAS NRO 7424',
            'ocupacion' => 'ESTUDIANTE',
            'estado_civil' => 'SOLTERA',
            'mrz_detectado' => true,
            'campos_detectados' => 11,
            'confianza_promedio' => 0.92,
            'texto_crudo' => [],
            'tiempo_ms' => 900,
        ]),
        '*/extraer-rostro' => Http::response('bytes-jpeg-recorte', 200, ['Content-Type' => 'image/jpeg']),
    ]);
}

function ejecutarJob(RegistroVerificacion $registro): void
{
    (new ProcesarVerificacion($registro))->handle(
        app(\App\Services\ServicioIA::class),
        app(\App\Services\EvaluadorVerificacion::class),
    );
}

test('todo correcto resulta en aprobada con 3 resultados y datos del documento', function () {
    fakeIa();

    ejecutarJob($this->registro);

    $this->registro->refresh();
    $this->usuaria->refresh();

    expect($this->registro->estado)->toBe('aprobada')
        ->and($this->registro->fecha_resolucion)->not->toBeNull()
        ->and($this->usuaria->estado_verificacion)->toBe('aprobada')
        ->and($this->registro->resultadosValidacion)->toHaveCount(3)
        ->and($this->registro->resultadosValidacion->pluck('resultado')->unique()->all())->toBe(['aprobado'])
        ->and($this->registro->datosDocumento->numero_cedula)->toBe('1234567')
        ->and($this->registro->datosDocumento->nombre_completo)->toBe('MARIA RENEE RODRIGUEZ GONZALEZ');
});

test('los campos completos de la cedula y el rostro extraido quedan persistidos', function () {
    fakeIa();

    ejecutarJob($this->registro);

    $datos = $this->registro->refresh()->datosDocumento;

    expect($datos->serie)->toBe('21333')
        ->and($datos->seccion)->toBe('11222')
        ->and($datos->lugar_nacimiento)->toBe('SANTA CRUZ DE LA SIERRA')
        ->and($datos->domicilio)->toBe('C. LAS PALMERAS NRO 7424')
        ->and($datos->ocupacion)->toBe('ESTUDIANTE')
        ->and($datos->estado_civil)->toBe('SOLTERA');

    $rostro = $this->registro->documentos()->where('tipo', 'rostro_cedula')->first();

    expect($rostro)->not->toBeNull()
        ->and($rostro->hash_archivo)->toBe(hash('sha256', 'bytes-jpeg-recorte'));
    Storage::disk('private')->assertExists($rostro->ruta_archivo);

    // El OCR debe haber recibido tambien el reverso.
    Http::assertSent(fn ($request) => str_contains($request->url(), 'ocr-cedula')
        && collect($request->data())->contains(fn ($d) => is_array($d) ? ($d['name'] ?? null) === 'reverso' : false));
});

test('si el recorte del rostro falla el flujo continua sin bloquearse', function () {
    Http::fake([
        '*/extraer-rostro' => Http::response(['detail' => 'sin rostro'], 422),
        '*/detectar-liveness' => Http::response(['es_real' => true, 'score' => 0.95, 'umbral_aplicado' => 0.85, 'tiempo_ms' => 1]),
        '*/verificar-rostro' => Http::response(['distancia' => 0.51, 'umbral_aplicado' => 0.68, 'coincide' => true, 'modelo' => 'ArcFace', 'tiempo_ms' => 1]),
        '*/ocr-cedula' => Http::response(['numero_cedula' => '1234567', 'serie' => null, 'seccion' => null, 'nombre_completo' => 'MARIA RENEE RODRIGUEZ', 'fecha_nacimiento' => '2003-04-05', 'fecha_emision' => '2023-11-01', 'fecha_vencimiento' => '2028-11-01', 'lugar_nacimiento' => null, 'domicilio' => null, 'ocupacion' => null, 'estado_civil' => null, 'mrz_detectado' => false, 'campos_detectados' => 5, 'confianza_promedio' => 0.9, 'texto_crudo' => [], 'tiempo_ms' => 1]),
    ]);

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('aprobada')
        ->and($this->registro->documentos()->where('tipo', 'rostro_cedula')->exists())->toBeFalse();
});

test('liveness fallido resulta en rechazada', function () {
    fakeIa(liveness: ['es_real' => false, 'score' => 0.40]);

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('rechazada')
        ->and($this->usuaria->refresh()->estado_verificacion)->toBe('rechazada')
        ->and($this->registro->resultadosValidacion()->where('tipo', 'liveness')->first()->resultado)->toBe('rechazado');
});

test('distancia facial en zona dudosa deriva a revision administrativa', function () {
    fakeIa(facial: ['distancia' => 0.71, 'coincide' => false]);

    ejecutarJob($this->registro);

    $facial = $this->registro->resultadosValidacion()->where('tipo', 'facial')->first();

    expect($this->registro->refresh()->estado)->toBe('en_revision')
        ->and($this->usuaria->refresh()->estado_verificacion)->toBe('en_revision')
        ->and($facial->resultado)->toBe('dudoso')
        ->and($facial->detalles['motivo'])->toContain('revisión humana');
});

test('distancia facial por encima del umbral dudoso resulta en rechazada', function () {
    fakeIa(facial: ['distancia' => 0.84, 'coincide' => false]);

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('rechazada')
        ->and($this->registro->resultadosValidacion()->where('tipo', 'facial')->first()->resultado)->toBe('rechazado');
});

test('ocr sin campos minimos deriva a revision administrativa', function () {
    fakeIa(ocr: ['numero_cedula' => null, 'nombre_completo' => null, 'campos_detectados' => 1]);

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('en_revision')
        ->and($this->registro->resultadosValidacion()->where('tipo', 'ocr')->first()->resultado)->toBe('dudoso');
});

test('ocr con fechas incoherentes deriva a revision con motivo estructural', function () {
    fakeIa(ocr: ['fecha_nacimiento' => '2025-01-01', 'fecha_emision' => '2023-11-01']);

    ejecutarJob($this->registro);

    $ocr = $this->registro->resultadosValidacion()->where('tipo', 'ocr')->first();

    expect($this->registro->refresh()->estado)->toBe('en_revision')
        ->and($ocr->resultado)->toBe('dudoso')
        ->and($ocr->detalles['motivo'])->toContain('incoherentes')
        ->and($ocr->detalles['errores_estructurales'])->not->toBeEmpty();
});

test('ocr con documento vencido deriva a revision con motivo de vigencia', function () {
    fakeIa(ocr: ['fecha_vencimiento' => now()->subMonth()->toDateString()]);

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('en_revision')
        ->and($this->registro->resultadosValidacion()->where('tipo', 'ocr')->first()->detalles['motivo'])
        ->toContain('vencido');
});

test('selfie sin rostro detectable (422) resulta en rechazada con motivo', function () {
    Http::fake([
        '*/detectar-liveness' => Http::response(['detail' => 'No se detecto un rostro en la captura.'], 422),
        '*/verificar-rostro' => Http::response(['detail' => 'No se detecto un rostro en una de las imagenes.'], 422),
        '*/ocr-cedula' => Http::response(['detail' => 'imagen corrupta'], 400),
    ]);

    ejecutarJob($this->registro);

    $liveness = $this->registro->resultadosValidacion()->where('tipo', 'liveness')->first();

    expect($this->registro->refresh()->estado)->toBe('rechazada')
        ->and($liveness->resultado)->toBe('rechazado')
        ->and($liveness->detalles['motivo'])->toContain('rostro');
});

test('umbral de aprobacion modificado en parametros_control cambia el veredicto', function () {
    App\Models\ParametroControl::where('clave', 'umbral_facial_aprobado')->update(['valor' => '0.40']);
    App\Models\ParametroControl::where('clave', 'umbral_facial_dudoso')->update(['valor' => '0.45']);
    fakeIa(); // distancia 0.51 > 0.45 → rechazada con los nuevos umbrales

    ejecutarJob($this->registro);

    expect($this->registro->refresh()->estado)->toBe('rechazada');
});

test('caida del servicio de IA lanza excepcion para que el job reintente', function () {
    Http::fake(['*' => Http::response(['detail' => 'boom'], 500)]);

    ejecutarJob($this->registro);
})->throws(RuntimeException::class);

test('al agotar reintentos el caso queda en revision y se loguea critico', function () {
    $job = new ProcesarVerificacion($this->registro);
    $job->failed(new RuntimeException('Servicio de IA caído'));

    expect($this->registro->refresh()->estado)->toBe('en_revision')
        ->and($this->usuaria->refresh()->estado_verificacion)->toBe('en_revision');
});

test('el paso 3 despacha el job de verificacion', function () {
    Queue::fake();
    Storage::fake('private');

    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'pendiente']);
    $this->actingAs($usuaria)->post('/verificacion/paso-1', [
        'name' => 'Maria', 'telefono' => '70011223', 'fecha_nacimiento' => '1995-01-01',
    ]);
    $this->actingAs($usuaria)->post('/verificacion/paso-2', [
        'anverso' => Illuminate\Http\UploadedFile::fake()->image('a.jpg'),
        'reverso' => Illuminate\Http\UploadedFile::fake()->image('r.jpg'),
    ]);
    $this->actingAs($usuaria)->post('/verificacion/paso-3', [
        'selfie' => Illuminate\Http\UploadedFile::fake()->image('s.jpg'),
    ]);

    Queue::assertPushed(ProcesarVerificacion::class, 1);
});
