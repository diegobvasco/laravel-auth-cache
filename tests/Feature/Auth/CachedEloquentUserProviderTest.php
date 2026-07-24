<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Cache\CacheManager;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;

function makeProvider(
    array $config = ['enabled' => true, 'ttl' => 60, 'prefix' => 'auth'],
    ?Repository $cacheRepository = null,
): array {
    $cacheRepository ??= Cache::store();
    $cacheConfiguration = CacheConfiguration::fromArray($config);
    $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

    $cacheManager = new CacheManager(
        cache: $cacheRepository,
        configuration: $cacheConfiguration,
    );

    $provider = new CachedEloquentUserProvider(
        hasher: app('hash'),
        model: User::class,
        cache: $cacheManager,
        keyGenerator: $cacheKeyGenerator,
        configuration: $cacheConfiguration,
    );

    return [$provider, $cacheKeyGenerator];
}

it('caches the user when cache is enabled', function () {
    $user = User::factory()->create();

    [$provider, $cacheKeyGenerator] = makeProvider();

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

    [$provider, $cacheKeyGenerator] = makeProvider(['enabled' => false, 'ttl' => 60, 'prefix' => 'auth']);

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('rehydrates model correctly from cached dto', function () {
    $user = User::factory()->create([
        'name' => 'Cached User',
        'email' => 'cached@example.com',
    ]);

    [$provider, $cacheKeyGenerator] = makeProvider();

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();
    expect($first->getKey())->toBe($user->getKey());
    expect($first->name)->toBe('Cached User');
    expect($first->email)->toBe('cached@example.com');

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);
    $cachedValue = Cache::get($cacheKey);

    expect($cachedValue)->toBeArray();
    expect($cachedValue['type'])->toBe('model');

    $second = $provider->retrieveById($user->id);

    expect($second->getKey())->toBe($user->getKey());
    expect($second->name)->toBe('Cached User');
    expect($second->email)->toBe('cached@example.com');
    expect($second->exists)->toBeTrue();

    expect($second)->not->toBe($first);
});

it('handles null returns from provider', function () {
    [$provider, $cacheKeyGenerator] = makeProvider();

    $result = $provider->retrieveById(999999);

    expect($result)->toBeNull();

    $cacheKey = $cacheKeyGenerator->generate(User::class, 999999);
    $cachedValue = Cache::get($cacheKey);

    expect($cachedValue)->toBeArray();
    expect($cachedValue['type'])->toBe('null');
});

it('works when the cache store restricts unserializable classes', function () {
    // Simulates Laravel's `cache.serializable_classes => false` security setting,
    // which converts every cached object into `__PHP_Incomplete_Class` on read.
    // The provider must therefore only store plain arrays of primitives.
    $store = new ArrayStore(true, false);
    $cacheRepository = new Repository($store);

    $user = User::factory()->create([
        'name' => 'Restricted Cache User',
        'email' => 'restricted@example.com',
    ]);

    [$provider, $cacheKeyGenerator] = makeProvider(cacheRepository: $cacheRepository);

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();
    expect($first->getKey())->toBe($user->getKey());

    $cacheKey = $cacheKeyGenerator->generate(User::class, $user->id);

    // The cached value must be a plain array, not a `__PHP_Incomplete_Class`.
    $cachedValue = $cacheRepository->get($cacheKey);
    expect($cachedValue)->toBeArray();
    expect($cachedValue)->not->toBeInstanceOf(__PHP_Incomplete_Class::class);

    // Second read hits the cache and must still return a valid model.
    $second = $provider->retrieveById($user->id);

    expect($second)->not->toBeNull();
    expect($second)->toBeInstanceOf(User::class);
    expect($second->getKey())->toBe($user->getKey());
    expect($second->name)->toBe('Restricted Cache User');
    expect($second->email)->toBe('restricted@example.com');
    expect($second->exists)->toBeTrue();
});
