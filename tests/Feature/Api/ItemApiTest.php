<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated users cannot access items api', function () {
    $this->getJson('/api/items')
        ->assertUnauthorized();
});

test('authenticated users can get their items', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/items')
        ->assertOk()
        ->assertJson([]);
});

test('authenticated users can create items', function () {
    $itemData = [
        'name' => 'Test Product',
        'price' => 99.99,
        'link' => 'https://example.com/product',
        'store' => 'Test Store',
    ];

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/items', $itemData)
        ->assertCreated()
        ->assertJson([
            'name' => 'Test Product',
            'price' => 99.99,
            'store' => 'Test Store',
        ]);

    $this->assertDatabaseHas('items', [
        'name' => 'Test Product',
        'user_id' => $this->user->id,
    ]);
});

test('users can only access their own items', function () {
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/items/{$item->id}")
        ->assertForbidden();
});

test('users can update their own items', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Name',
            'purchased' => true,
        ])
        ->assertOk()
        ->assertJson([
            'name' => 'Updated Name',
            'purchased' => true,
        ]);
});

test('users can delete their own items', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/items/{$item->id}")
        ->assertOk();

    $this->assertDatabaseMissing('items', ['id' => $item->id]);
});

test('api login works correctly', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

test('api login fails with invalid credentials', function () {
    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnauthorized()
        ->assertJson(['message' => 'Invalid credentials']);
});
