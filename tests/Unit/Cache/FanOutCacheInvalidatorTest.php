<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Cache\FanOutCacheInvalidator;
use DiegoVasconcelos\AuthCache\Cache\ProviderCacheRegistry;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Cache;

function makeFanOutInvalidator(
    ProviderCacheRegistry $registry,
    ?CacheConfigurationInterface $fallback = null,
    ?Closure $providerConfigurations = null,
): FanOutCacheInvalidator {
    return new FanOutCacheInvalidator(
        registry: $registry,
        fallbackConfiguration: $fallback ?? CacheConfiguration::fromArray([]),
        keyGeneratorResolver: fn (CacheConfigurationInterface $configuration) => new CacheKeyGenerator($configuration),
        providerConfigurations: $providerConfigurations ?? fn (): array => [],
    );
}

it('forgets the key on every registered store', function () {
    config()->set('cache.stores.alpha', ['driver' => 'array']);
    config()->set('cache.stores.beta', ['driver' => 'array']);

    $registry = new ProviderCacheRegistry();
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'a', 'store' => 'alpha']));
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'b', 'store' => 'beta']));

    Cache::store('alpha')->put('a.user.1', 'value', 60);
    Cache::store('beta')->put('b.user.1', 'value', 60);

    makeFanOutInvalidator($registry)->invalidate(new User(), 1);

    expect(Cache::store('alpha')->has('a.user.1'))->toBeFalse();
    expect(Cache::store('beta')->has('b.user.1'))->toBeFalse();
});

it('uses the default store for entries without a store', function () {
    $registry = new ProviderCacheRegistry();
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth']));

    Cache::put('auth.user.2', 'value', 60);

    makeFanOutInvalidator($registry)->invalidate(new User(), 2);

    expect(Cache::has('auth.user.2'))->toBeFalse();
});

it('derives entries from the auth provider configuration closure', function () {
    config()->set('cache.stores.alpha', ['driver' => 'array']);

    $registry = new ProviderCacheRegistry();

    Cache::store('alpha')->put('remote.user.3', 'value', 60);

    makeFanOutInvalidator(
        $registry,
        providerConfigurations: fn (): array => [
            CacheConfiguration::fromArray(['prefix' => 'remote', 'store' => 'alpha']),
        ],
    )->invalidate(new User(), 3);

    expect(Cache::store('alpha')->has('remote.user.3'))->toBeFalse();
    expect($registry->entries())->toHaveCount(1);
});

it('falls back to the given configuration when nothing is registered', function () {
    $fallback = CacheConfiguration::fromArray(['prefix' => 'fallback']);

    Cache::put('fallback.user.4', 'value', 60);

    makeFanOutInvalidator(new ProviderCacheRegistry(), fallback: $fallback)->invalidate(new User(), 4);

    expect(Cache::has('fallback.user.4'))->toBeFalse();
});

it('throws when a registered store does not exist', function () {
    $registry = new ProviderCacheRegistry();
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth', 'store' => 'nonexistent_store']));

    makeFanOutInvalidator($registry)->invalidate(new User(), 5);
})->throws(InvalidArgumentException::class);

it('resolves key generators through the given resolver', function () {
    $registry = new ProviderCacheRegistry();
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth']));

    Cache::put('custom.user.6', 'value', 60);

    $invalidator = new FanOutCacheInvalidator(
        registry: $registry,
        fallbackConfiguration: CacheConfiguration::fromArray([]),
        keyGeneratorResolver: fn (CacheConfigurationInterface $configuration) => new class('custom.user.6') implements CacheKeyGeneratorInterface
        {
            public function __construct(private string $key) {}

            public function generate(string $modelClass, mixed $identifier): string
            {
                return $this->key;
            }
        },
        providerConfigurations: fn (): array => [],
    );

    $invalidator->invalidate(new User(), 6);

    expect(Cache::has('custom.user.6'))->toBeFalse();
});
