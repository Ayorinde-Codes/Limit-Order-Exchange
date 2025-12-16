<?php

use App\Models\Asset;
use App\Models\User;

test('guest cannot access profile', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401);
});

test('user can get profile', function () {
    $user = User::factory()->create([
        'balance' => 50000.00,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.balance', 50000);
});

test('profile includes user assets', function () {
    $user = User::factory()->create(['balance' => 10000.00]);

    Asset::factory()->create([
        'user_id' => $user->id,
        'symbol' => 'BTC',
        'amount' => 2.5,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.balance', 10000)
        ->assertJsonCount(1, 'data.assets');
});
