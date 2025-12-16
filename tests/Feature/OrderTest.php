<?php

use App\Models\Asset;
use App\Models\User;

test('guest cannot access orders', function () {
    $response = $this->getJson('/api/orders?symbol=BTC');

    $response->assertStatus(401);
});

test('user can get orderbook', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/orders?symbol=BTC');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['buy_orders', 'sell_orders']]);
});

test('user can create buy order with sufficient balance', function () {
    $user = User::factory()->create(['balance' => 100000.00]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
        'symbol' => 'BTC',
        'side' => 'buy',
        'price' => 50000,
        'amount' => 0.1,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'symbol' => 'BTC',
        'side' => 'buy',
    ]);
});

test('user cannot create buy order with insufficient balance', function () {
    $user = User::factory()->create(['balance' => 100.00]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
        'symbol' => 'BTC',
        'side' => 'buy',
        'price' => 50000,
        'amount' => 1,
    ]);

    $response->assertStatus(422);
});

test('user can create sell order with sufficient assets', function () {
    $user = User::factory()->create(['balance' => 1000.00]);

    Asset::factory()->create([
        'user_id' => $user->id,
        'symbol' => 'BTC',
        'amount' => 2.0,
        'locked_amount' => 0,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
        'symbol' => 'BTC',
        'side' => 'sell',
        'price' => 50000,
        'amount' => 0.5,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'symbol' => 'BTC',
        'side' => 'sell',
    ]);
});

test('user cannot create sell order with insufficient assets', function () {
    $user = User::factory()->create(['balance' => 1000.00]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
        'symbol' => 'BTC',
        'side' => 'sell',
        'price' => 50000,
        'amount' => 1,
    ]);

    $response->assertStatus(422);
});

test('guest cannot access order history', function () {
    $response = $this->getJson('/api/orders/history');

    $response->assertStatus(401);
});

test('user can get order history', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/orders/history');

    $response->assertStatus(200);
});
