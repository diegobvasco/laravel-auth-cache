<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\Exceptions\CorruptedCacheEntry;
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

    expect($dto->modelClass)->toBe(User::class);
    expect($dto->attributes)->toBe($user->getAttributes());
    expect($dto->exists)->toBeTrue();
});

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

it('converts to a plain array of primitives', function () {
    $user = User::factory()->create([
        'name' => 'Array User',
        'email' => 'array@example.com',
    ]);

    $dto = CachedUserData::fromModel($user);
    $array = $dto->toArray();

    expect($array)->toBeArray();
    expect($array['modelClass'])->toBe(User::class);
    expect($array['attributes'])->toBe($user->getAttributes());
    expect($array['exists'])->toBeTrue();
});

it('reconstructs from array and rehydrates the model', function () {
    $user = User::factory()->create([
        'name' => 'From Array User',
        'email' => 'from-array@example.com',
    ]);

    $dto = CachedUserData::fromModel($user);
    $reconstructed = CachedUserData::fromArray($dto->toArray());
    $rehydrated = $reconstructed->toAuthenticatable();

    expect($rehydrated)->toBeInstanceOf(User::class);
    expect($rehydrated->getKey())->toBe($user->getKey());
    expect($rehydrated->name)->toBe('From Array User');
    expect($rehydrated->email)->toBe('from-array@example.com');
});

it('survives a restricted unserialize roundtrip as array', function () {
    // Mirrors Laravel's `cache.serializable_classes => false` setting, where
    // objects become `__PHP_Incomplete_Class` but plain arrays survive.
    $user = User::factory()->create();

    $payload = serialize(CachedUserData::fromModel($user)->toArray());
    $result = unserialize($payload, ['allowed_classes' => false]);

    expect($result)->toBeArray();

    $rehydrated = CachedUserData::fromArray($result)->toAuthenticatable();

    expect($rehydrated)->toBeInstanceOf(User::class);
    expect($rehydrated->getKey())->toBe($user->getKey());
});

it('throws corrupted cache entry when the payload is missing fields', function () {
    CachedUserData::fromArray([]);
})->throws(CorruptedCacheEntry::class, 'Cached user payload is missing or has invalid fields.');

it('throws corrupted cache entry when the payload has invalid field types', function () {
    CachedUserData::fromArray([
        'modelClass' => 123,
        'attributes' => ['id' => 1],
    ]);
})->throws(CorruptedCacheEntry::class);

it('throws corrupted cache entry when the model class no longer exists', function () {
    $dto = CachedUserData::fromArray([
        'modelClass' => 'App\\Gone\\User',
        'attributes' => ['id' => 1],
        'exists' => true,
    ]);

    $dto->toAuthenticatable();
})->throws(CorruptedCacheEntry::class, 'does not exist or is not an Eloquent authenticatable model');

it('throws corrupted cache entry when the model class is not an Eloquent model', function () {
    $dto = CachedUserData::fromArray([
        'modelClass' => stdClass::class,
        'attributes' => ['id' => 1],
        'exists' => true,
    ]);

    $dto->toAuthenticatable();
})->throws(CorruptedCacheEntry::class);
