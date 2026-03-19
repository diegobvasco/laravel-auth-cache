<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Auth\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Auth\CacheManager;
use DiegoVasconcelos\AuthCache\DTOs\CachedUserData;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Cache;

it('caches the user when cache is enabled', function () {
    $user = User::factory()->create();

    $cacheRepository = Cache::store();

    $cacheConfiguration = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60, 'prefix' => 'auth']);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
        keyGenerator: $cacheKeyGenerator
    );

    $cacheInvalidator = new CacheInvalidator(
        cache: $cacheRepository,
        keyGenerator: $cacheKeyGenerator
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cacheManager: $cacheManager,
        cacheInvalidator: $cacheInvalidator
    );

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();
    expect($first->getKey())->toBe($user->getKey());

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);

    expect(Cache::has($cacheKey))->toBeTrue();

    $second = $provider->retrieveById($user->id);

    expect($second->getKey())->toBe($user->getKey());
});

it('does not cache the user when cache is disabled', function () {
    $user = User::factory()->create();

    $cacheRepository = Cache::store();

    $cacheConfiguration = CacheConfiguration::fromArray(['enabled' => false, 'ttl' => 60, 'prefix' => 'auth']);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
        keyGenerator: $cacheKeyGenerator
    );

    $cacheInvalidator = new CacheInvalidator(
        cache: $cacheRepository,
        keyGenerator: $cacheKeyGenerator
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cacheManager: $cacheManager,
        cacheInvalidator: $cacheInvalidator
    );

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('removes cache when requested', function () {
    $user = User::factory()->create();

    $cacheRepository = Cache::store();

    $cacheConfiguration = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60, 'prefix' => 'auth']);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
        keyGenerator: $cacheKeyGenerator
    );

    $cacheInvalidator = new CacheInvalidator(
        cache: $cacheRepository,
        keyGenerator: $cacheKeyGenerator
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cacheManager: $cacheManager,
        cacheInvalidator: $cacheInvalidator
    );

    $provider->retrieveById($user->id);

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);

    expect(Cache::has($cacheKey))->toBeTrue();

    $provider->removeCache($user, $user->id);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('rehydrates model correctly from cached dto', function () {
    $user = User::factory()->create([
        'name' => 'Cached User',
        'email' => 'cached@example.com',
    ]);

    $cacheRepository = Cache::store();
    $cacheConfiguration = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60, 'prefix' => 'auth']);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
        keyGenerator: $cacheKeyGenerator
    );

    $cacheInvalidator = new CacheInvalidator(
        cache: $cacheRepository,
        keyGenerator: $cacheKeyGenerator
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cacheManager: $cacheManager,
        cacheInvalidator: $cacheInvalidator
    );

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();
    expect($first->getKey())->toBe($user->getKey());
    expect($first->name)->toBe('Cached User');
    expect($first->email)->toBe('cached@example.com');

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);
    $cachedValue = Cache::get($cacheKey);

    expect($cachedValue)->toBeInstanceOf(CachedUserData::class);

    $second = $provider->retrieveById($user->id);

    expect($second->getKey())->toBe($user->getKey());
    expect($second->name)->toBe('Cached User');
    expect($second->email)->toBe('cached@example.com');
    expect($second->exists)->toBeTrue();

    expect($second)->not->toBe($first);
});

it('handles null returns from provider', function () {
    $cacheRepository = Cache::store();
    $cacheConfiguration = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60, 'prefix' => 'auth']);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
        keyGenerator: $cacheKeyGenerator
    );

    $cacheInvalidator = new CacheInvalidator(
        cache: $cacheRepository,
        keyGenerator: $cacheKeyGenerator
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cacheManager: $cacheManager,
        cacheInvalidator: $cacheInvalidator
    );

    $result = $provider->retrieveById(999999);

    expect($result)->toBeNull();

    $cacheKey = $cacheKeyGenerator->generate(User::class, 999999);
    $cachedValue = Cache::get($cacheKey);

    expect($cachedValue)->toBeInstanceOf(CachedUserData::class);
    expect($cachedValue->type)->toBe('null');
});
