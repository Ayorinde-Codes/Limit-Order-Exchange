<?php

use App\Models\User;

test('user can view login page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('p@ssw0rd'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'p@ssw0rd',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('p@ssw0rd'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

