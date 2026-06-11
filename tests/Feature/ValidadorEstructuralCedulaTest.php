<?php

use App\Models\ParametroControl;
use App\Services\ValidadorEstructuralCedula;
use Database\Seeders\ParametrosControlSeeder;

beforeEach(function () {
    $this->seed(ParametrosControlSeeder::class);
    $this->validador = new ValidadorEstructuralCedula;
    $this->datosValidos = [
        'numero_cedula' => '1234567',
        'nombre_completo' => 'MARIA RENEE RODRIGUEZ GONZALEZ',
        'fecha_nacimiento' => '2003-04-05',
        'fecha_emision' => '2023-11-01',
        'fecha_vencimiento' => now()->addYears(2)->toDateString(),
    ];
});

test('datos completos y coherentes son validos', function () {
    $resultado = $this->validador->validar($this->datosValidos);

    expect($resultado['valido'])->toBeTrue()
        ->and($resultado['errores'])->toBeEmpty();
});

test('numero de cedula con formato invalido es detectado', function (string $numero) {
    $resultado = $this->validador->validar(['numero_cedula' => $numero] + $this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('formato esperado');
})->with(['123', '123456789', 'AB34567', '12-34567']);

test('fecha de nacimiento posterior a la emision es incoherente', function () {
    $resultado = $this->validador->validar([
        'fecha_nacimiento' => '2024-01-01',
        'fecha_emision' => '2023-11-01',
    ] + $this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('incoherentes');
});

test('documento vencido es detectado', function () {
    $resultado = $this->validador->validar([
        'fecha_vencimiento' => now()->subDay()->toDateString(),
    ] + $this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('vencido');
});

test('nombre demasiado corto es detectado', function () {
    $resultado = $this->validador->validar(['nombre_completo' => 'MA'] + $this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('demasiado corto');
});

test('campos obligatorios faltantes son reportados con sus nombres', function () {
    $resultado = $this->validador->validar([
        'numero_cedula' => null,
        'fecha_vencimiento' => null,
    ] + $this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('número de cédula')
        ->and($resultado['errores'][0])->toContain('fecha de vencimiento');
});

test('varios errores se acumulan en la misma validacion', function () {
    $resultado = $this->validador->validar([
        'numero_cedula' => '12',
        'nombre_completo' => 'MA',
        'fecha_nacimiento' => '2024-05-05',
        'fecha_emision' => '2023-11-01',
        'fecha_vencimiento' => '2020-01-01',
    ]);

    expect($resultado['valido'])->toBeFalse()
        ->and(count($resultado['errores']))->toBeGreaterThanOrEqual(4);
});

test('la lista de campos obligatorios es configurable desde parametros_control', function () {
    ParametroControl::where('clave', 'campos_obligatorios')->update(['valor' => 'numero_cedula']);

    $resultado = $this->validador->validar([
        'numero_cedula' => '1234567',
        'nombre_completo' => 'MARIA RODRIGUEZ',
        'fecha_nacimiento' => null,
        'fecha_emision' => null,
        'fecha_vencimiento' => null,
    ]);

    expect($resultado['valido'])->toBeTrue();
});

test('regex de cedula modificada en parametros_control cambia el resultado', function () {
    ParametroControl::where('clave', 'regex_cedula')->update(['valor' => '^[0-9]{10}$']);

    $resultado = $this->validador->validar($this->datosValidos);

    expect($resultado['valido'])->toBeFalse()
        ->and($resultado['errores'][0])->toContain('formato esperado');
});
