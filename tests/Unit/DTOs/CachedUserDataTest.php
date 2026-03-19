<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\DTOs\CachedUserData;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates dto from eloquent model', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $dto = CachedUserData::fromModel($user);

    expect($dto->type)->toBe('model');
    expect($dto->modelClass)->toBe(User::class);
    expect($dto->attributes)->toBe($user->getAttributes());
    expect($dto->exists)->toBeTrue();
    expect($dto->value)->toBeNull();
});

it('creates dto from string value', function () {
    $token = 'api_token_12345';

    $dto = CachedUserData::fromString($token);

    expect($dto->type)->toBe('string');
    expect($dto->value)->toBe($token);
    expect($dto->modelClass)->toBeNull();
    expect($dto->attributes)->toBeNull();
    expect($dto->exists)->toBeTrue();
});

it('creates dto from null value', function () {
    $dto = CachedUserData::fromNull();

    expect($dto->type)->toBe('null');
    expect($dto->value)->toBeNull();
    expect($dto->modelClass)->toBeNull();
    expect($dto->attributes)->toBeNull();
    expect($dto->exists)->toBeFalse();
});

it('creates dto using from factory method with model', function () {
    $user = User::factory()->create();

    $dto = CachedUserData::from($user);

    expect($dto->type)->toBe('model');
    expect($dto->modelClass)->toBe(User::class);
});

it('creates dto using from factory method with string', function () {
    $token = 'test_token';

    $dto = CachedUserData::from($token);

    expect($dto->type)->toBe('string');
    expect($dto->value)->toBe($token);
});

it('creates dto using from factory method with null', function () {
    $dto = CachedUserData::from(null);

    expect($dto->type)->toBe('null');
});

it('throws exception when creating dto from unsupported type', function () {
    CachedUserData::from(['array' => 'value']);
})->throws(InvalidArgumentException::class);

it('rehydrates model from dto correctly', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $dto = CachedUserData::fromModel($user);
    $rehydrated = $dto->toAuthenticatable();

    expect($rehydrated)->toBeInstanceOf(User::class);
    expect($rehydrated->getKey())->toBe($user->getKey());
    expect($rehydrated->name)->toBe('Original Name');
    expect($rehydrated->email)->toBe('original@example.com');
    expect($rehydrated->exists)->toBeTrue();
});

it('rehydrates model preserves exists flag', function () {
    $user = new User([
        'name' => 'New User',
        'email' => 'new@example.com',
    ]);
    $user->exists = false;

    $dto = CachedUserData::fromModel($user);
    $rehydrated = $dto->toAuthenticatable();

    expect($rehydrated->exists)->toBeFalse();
});

it('returns string value from dto', function () {
    $token = 'api_token_xyz';

    $dto = CachedUserData::fromString($token);
    $result = $dto->toAuthenticatable();

    expect($result)->toBe($token);
});

it('returns null from dto', function () {
    $dto = CachedUserData::fromNull();
    $result = $dto->toAuthenticatable();

    expect($result)->toBeNull();
});

it('preserves model attributes after roundtrip', function () {
    $user = User::factory()->create([
        'name' => 'Roundtrip User',
        'email' => 'roundtrip@example.com',
        'password' => bcrypt('secret'),
    ]);

    $originalAttributes = $user->getAttributes();

    $dto = CachedUserData::fromModel($user);
    $rehydrated = $dto->toAuthenticatable();

    expect($rehydrated->getAttributes())->toBe($originalAttributes);
});
