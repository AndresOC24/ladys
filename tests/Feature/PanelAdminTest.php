<?php

use App\Models\Rol;
use App\Models\Usuaria;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');

    $rolAdmin = Rol::create(['nombre' => 'administrador']);
    $rolPasajera = Rol::create(['nombre' => 'pasajera']);

    $this->admin = Usuaria::factory()->aprobada()->create(['rol_id' => $rolAdmin->id]);
    $this->pasajera = Usuaria::factory()->create(['rol_id' => $rolPasajera->id, 'estado_verificacion' => 'en_revision']);

    $this->registro = $this->pasajera->registrosVerificacion()->create([
        'fecha_inicio' => now(),
        'estado' => 'en_revision',
    ]);

    foreach (['selfie' => 'selfies', 'anverso' => 'cedulas', 'reverso' => 'cedulas'] as $tipo => $carpeta) {
        $ruta = "{$carpeta}/{$this->registro->id}_{$tipo}_1.jpg";
        Storage::disk('private')->put($ruta, 'imagen-de-prueba');
        $this->registro->documentos()->create([
            'tipo' => $tipo,
            'ruta_archivo' => $ruta,
            'hash_archivo' => hash('sha256', 'imagen-de-prueba'),
        ]);
    }

    $this->registro->resultadosValidacion()->createMany([
        ['tipo' => 'liveness', 'puntaje' => 0.93, 'resultado' => 'aprobado', 'detalles' => ['umbral_aplicado' => 0.85]],
        ['tipo' => 'facial', 'puntaje' => 0.71, 'resultado' => 'dudoso', 'detalles' => ['umbral_aprobado' => 0.68, 'umbral_dudoso' => 0.75, 'motivo' => 'Zona dudosa; requiere revisión humana.']],
        ['tipo' => 'ocr', 'puntaje' => 0.90, 'resultado' => 'aprobado', 'detalles' => []],
    ]);
});

test('el admin ve el dashboard con la bandeja de pendientes', function () {
    $response = $this->actingAs($this->admin, 'backpack')->get('/admin/dashboard');

    $response->assertOk()
        ->assertSee('Bandeja de revisión administrativa')
        ->assertSee($this->pasajera->name);
});

test('los cruds del panel cargan para el admin', function (string $ruta) {
    $this->actingAs($this->admin, 'backpack')->get($ruta)->assertOk();
})->with(['/admin/usuaria', '/admin/registro-verificacion', '/admin/parametro-control', '/admin/rol']);

test('una pasajera no puede entrar a los cruds del panel', function () {
    $response = $this->actingAs($this->pasajera, 'backpack')->get('/admin/usuaria');

    $response->assertRedirect();
});

test('la pagina de revision muestra evidencias, puntajes y datos', function () {
    $this->registro->datosDocumento()->create([
        'numero_cedula' => '1234567',
        'nombre_completo' => 'MARIA RENEE RODRIGUEZ GONZALEZ',
    ]);

    $response = $this->actingAs($this->admin, 'backpack')->get('/admin/revision/'.$this->registro->id);

    $response->assertOk()
        ->assertSee('Revisión del registro #'.$this->registro->id)
        ->assertSee('0.7100')
        ->assertSee('1234567')
        ->assertSee('Zona dudosa');
});

test('la ruta de documentos sirve la imagen privada al admin y la niega a una pasajera', function () {
    $documento = $this->registro->documentos()->where('tipo', 'selfie')->first();

    $this->actingAs($this->admin, 'backpack')
        ->get('/admin/documento/'.$documento->id)
        ->assertOk();

    $this->actingAs($this->pasajera, 'backpack')
        ->get('/admin/documento/'.$documento->id)
        ->assertRedirect();
});

test('aprobar un caso registra la revision y actualiza los estados', function () {
    $response = $this->actingAs($this->admin, 'backpack')->post('/admin/revision/'.$this->registro->id, [
        'decision' => 'aprobada',
        'observaciones' => null,
    ]);

    $response->assertRedirect();

    $revision = $this->registro->refresh()->revisionAdministrativa;

    expect($revision)->not->toBeNull()
        ->and($revision->decision)->toBe('aprobada')
        ->and($revision->administrador_id)->toBe($this->admin->id)
        ->and($revision->fecha_revision)->not->toBeNull()
        ->and($this->registro->estado)->toBe('aprobada')
        ->and($this->registro->fecha_resolucion)->not->toBeNull()
        ->and($this->pasajera->refresh()->estado_verificacion)->toBe('aprobada');
});

test('rechazar exige observaciones', function () {
    $response = $this->actingAs($this->admin, 'backpack')
        ->from('/admin/revision/'.$this->registro->id)
        ->post('/admin/revision/'.$this->registro->id, [
            'decision' => 'rechazada',
            'observaciones' => '',
        ]);

    $response->assertSessionHasErrors('observaciones');
    expect($this->registro->refresh()->estado)->toBe('en_revision');
});

test('solicitar reenvio deja a la usuaria rechazada con las observaciones registradas', function () {
    $this->actingAs($this->admin, 'backpack')->post('/admin/revision/'.$this->registro->id, [
        'decision' => 'solicitar_reenvio',
        'observaciones' => 'La foto del anverso está borrosa, vuelve a capturarla.',
    ]);

    $revision = $this->registro->refresh()->revisionAdministrativa;

    expect($revision->decision)->toBe('solicitar_reenvio')
        ->and($revision->observaciones)->toContain('borrosa')
        ->and($this->registro->estado)->toBe('rechazada')
        ->and($this->pasajera->refresh()->estado_verificacion)->toBe('rechazada');
});

test('la usuaria rechazada ve las observaciones de la revision en su resultado', function () {
    $this->actingAs($this->admin, 'backpack')->post('/admin/revision/'.$this->registro->id, [
        'decision' => 'solicitar_reenvio',
        'observaciones' => 'La foto del anverso está borrosa, vuelve a capturarla.',
    ]);

    $response = $this->actingAs($this->pasajera->refresh())->get('/verificacion/resultado');

    $response->assertOk()->assertSee('borrosa');
});
