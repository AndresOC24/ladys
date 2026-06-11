<?php

use App\Models\Usuaria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    $this->usuaria = Usuaria::factory()->create([
        'estado_verificacion' => 'pendiente',
        'telefono' => '70011223',
        'fecha_nacimiento' => '1995-01-01',
    ]);
});

function completarPaso1($test): void
{
    $test->actingAs($test->usuaria)->post('/verificacion/paso-1', [
        'name' => 'Maria Verificada',
        'telefono' => '70011223',
        'fecha_nacimiento' => '1995-01-01',
    ]);
}

function completarPaso2($test): void
{
    $test->actingAs($test->usuaria)->post('/verificacion/paso-2', [
        'anverso' => UploadedFile::fake()->image('anverso.jpg', 800, 500),
        'reverso' => UploadedFile::fake()->image('reverso.jpg', 800, 500),
    ]);
}

test('paso 1 guarda datos y abre el registro de verificacion', function () {
    $response = $this->actingAs($this->usuaria)->post('/verificacion/paso-1', [
        'name' => 'Maria Verificada',
        'telefono' => '70099887',
        'fecha_nacimiento' => '1996-02-02',
    ]);

    $response->assertRedirect(route('verificacion.paso2'));

    $this->usuaria->refresh();
    expect($this->usuaria->name)->toBe('Maria Verificada')
        ->and($this->usuaria->telefono)->toBe('70099887')
        ->and($this->usuaria->registrosVerificacion()->count())->toBe(1)
        ->and($this->usuaria->registrosVerificacion()->first()->estado)->toBe('pendiente')
        ->and($this->usuaria->registrosVerificacion()->first()->fecha_inicio)->not->toBeNull();
});

test('paso 1 repetido no duplica el registro activo', function () {
    completarPaso1($this);
    completarPaso1($this);

    expect($this->usuaria->registrosVerificacion()->count())->toBe(1);
});

test('paso 2 sin registro activo redirige al paso 1', function () {
    $response = $this->actingAs($this->usuaria)->get('/verificacion/paso-2');

    $response->assertRedirect(route('verificacion.paso1'));
});

test('paso 2 guarda anverso y reverso con hash sha256', function () {
    completarPaso1($this);

    $response = $this->actingAs($this->usuaria)->post('/verificacion/paso-2', [
        'anverso' => UploadedFile::fake()->image('anverso.jpg', 800, 500),
        'reverso' => UploadedFile::fake()->image('reverso.jpg', 800, 500),
    ]);

    $response->assertRedirect(route('verificacion.paso3'));

    $registro = $this->usuaria->registrosVerificacion()->first();
    $documentos = $registro->documentos;

    expect($documentos)->toHaveCount(2)
        ->and($documentos->pluck('tipo')->sort()->values()->all())->toBe(['anverso', 'reverso']);

    foreach ($documentos as $documento) {
        Storage::disk('private')->assertExists($documento->ruta_archivo);
        expect($documento->hash_archivo)
            ->toBe(hash('sha256', Storage::disk('private')->get($documento->ruta_archivo)))
            ->and($documento->ruta_archivo)->toStartWith('cedulas/')
            ->and($documento->ruta_archivo)->toContain("{$registro->id}_{$documento->tipo}_");
    }
});

test('paso 2 rechaza archivos que no son imagen', function () {
    completarPaso1($this);

    $response = $this->actingAs($this->usuaria)->post('/verificacion/paso-2', [
        'anverso' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        'reverso' => UploadedFile::fake()->image('reverso.jpg'),
    ]);

    $response->assertSessionHasErrors('anverso');
});

test('paso 2 rechaza imagenes de mas de 5MB', function () {
    completarPaso1($this);

    $response = $this->actingAs($this->usuaria)->post('/verificacion/paso-2', [
        'anverso' => UploadedFile::fake()->image('anverso.jpg')->size(6000),
        'reverso' => UploadedFile::fake()->image('reverso.jpg'),
    ]);

    $response->assertSessionHasErrors('anverso');
});

test('reenviar paso 2 reemplaza los documentos sin duplicarlos', function () {
    completarPaso1($this);
    completarPaso2($this);
    completarPaso2($this);

    $registro = $this->usuaria->registrosVerificacion()->first();

    expect($registro->documentos()->where('tipo', 'anverso')->count())->toBe(1)
        ->and($registro->documentos()->where('tipo', 'reverso')->count())->toBe(1);
});

test('paso 3 sin cedula cargada redirige al paso 2', function () {
    completarPaso1($this);

    $response = $this->actingAs($this->usuaria)->get('/verificacion/paso-3');

    $response->assertRedirect(route('verificacion.paso2'));
});

test('paso 3 guarda la selfie y marca el registro en proceso', function () {
    Illuminate\Support\Facades\Queue::fake();
    completarPaso1($this);
    completarPaso2($this);

    $response = $this->actingAs($this->usuaria)->post('/verificacion/paso-3', [
        'selfie' => UploadedFile::fake()->image('selfie.jpg', 1280, 960),
    ]);

    $response->assertRedirect(route('verificacion.procesando'));

    $this->usuaria->refresh();
    $registro = $this->usuaria->registrosVerificacion()->first();

    expect($this->usuaria->estado_verificacion)->toBe('en_proceso')
        ->and($registro->estado)->toBe('en_proceso')
        ->and($registro->documentos()->where('tipo', 'selfie')->count())->toBe(1);

    $selfie = $registro->documentos()->where('tipo', 'selfie')->first();
    Storage::disk('private')->assertExists($selfie->ruta_archivo);
    expect($selfie->ruta_archivo)->toStartWith('selfies/');
});

test('endpoint de estado json responde el estado actual', function () {
    completarPaso1($this);

    $response = $this->actingAs($this->usuaria)->getJson('/verificacion/estado-json');

    $response->assertOk()->assertJson([
        'estado_usuaria' => 'pendiente',
        'estado_registro' => 'pendiente',
    ]);
});

test('pantalla procesando se muestra solo en proceso', function () {
    Illuminate\Support\Facades\Queue::fake();
    completarPaso1($this);
    completarPaso2($this);
    $this->actingAs($this->usuaria)->post('/verificacion/paso-3', [
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
    ]);

    $this->actingAs($this->usuaria)->get('/verificacion/procesando')->assertOk();

    $this->usuaria->update(['estado_verificacion' => 'en_revision']);
    $this->actingAs($this->usuaria)
        ->get('/verificacion/procesando')
        ->assertRedirect(route('verificacion.resultado'));
});

test('pantalla resultado muestra en revision con su mensaje', function () {
    completarPaso1($this);
    $this->usuaria->update(['estado_verificacion' => 'en_revision']);
    $this->usuaria->registrosVerificacion()->first()->update(['estado' => 'en_revision']);

    $response = $this->actingAs($this->usuaria)->get('/verificacion/resultado');

    $response->assertOk()->assertSee('en revisión');
});
