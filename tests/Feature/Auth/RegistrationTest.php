<?php

use App\Models\Rol;

test('registration screen can be rendered', function () {
    Rol::create(['nombre' => 'pasajera']);

    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $rol = Rol::create(['nombre' => 'pasajera']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol_id' => $rol->id,
        'telefono' => '70012345',
        'fecha_nacimiento' => '1995-05-10',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->user()->estado_verificacion)->toBe('pendiente')
        ->and(auth()->user()->rol_id)->toBe($rol->id);
});

test('registration rejects administrador role', function () {
    $rol = Rol::create(['nombre' => 'administrador']);

    $response = $this->post('/register', [
        'name' => 'Intrusa',
        'email' => 'intrusa@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol_id' => $rol->id,
        'telefono' => '70012345',
        'fecha_nacimiento' => '1995-05-10',
    ]);

    $response->assertSessionHasErrors('rol_id');
    $this->assertGuest();
});

test('registration rejects users under 18', function () {
    $rol = Rol::create(['nombre' => 'pasajera']);

    $response = $this->post('/register', [
        'name' => 'Menor',
        'email' => 'menor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol_id' => $rol->id,
        'telefono' => '70012345',
        'fecha_nacimiento' => now()->subYears(17)->toDateString(),
    ]);

    $response->assertSessionHasErrors('fecha_nacimiento');
    $this->assertGuest();
});
