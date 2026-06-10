<?php

use App\Models\Usuaria;

test('unverified user is redirected from dashboard to verification status', function () {
    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'pendiente']);

    $response = $this->actingAs($usuaria)->get('/dashboard');

    $response->assertRedirect(route('verificacion.estado'));
});

test('approved user can access dashboard', function () {
    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'aprobada']);

    $response = $this->actingAs($usuaria)->get('/dashboard');

    $response->assertStatus(200);
});

test('unverified user can see verification status page', function () {
    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'en_revision']);

    $response = $this->actingAs($usuaria)->get('/verificacion/estado');

    $response->assertStatus(200);
    $response->assertSee('revisión administrativa');
});

test('approved user is redirected from status page to dashboard', function () {
    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'aprobada']);

    $response = $this->actingAs($usuaria)->get('/verificacion/estado');

    $response->assertRedirect(route('dashboard'));
});

test('unverified user can logout', function () {
    $usuaria = Usuaria::factory()->create(['estado_verificacion' => 'pendiente']);

    $response = $this->actingAs($usuaria)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});
