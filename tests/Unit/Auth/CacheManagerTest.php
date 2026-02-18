<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Auth\CacheManager;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInterface;
use Illuminate\Contracts\Cache\Repository;

it('implements cache interface', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    expect($manager)->toBeInstanceOf(CacheInterface::class);
});

it('caches value when enabled', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('remember')
        ->once()
        ->with('test.key', \Mockery::type(\DateTimeInterface::class), \Mockery::type('callable'))
        ->andReturn('cached-value');

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    $result = $manager->remember('test.key', now()->addMinutes(60), fn () => 'original-value');

    expect($result)->toBe('cached-value');
});

it('does not cache when disabled', function () {
    $config = CacheConfiguration::fromArray(['enabled' => false, 'ttl' => 60]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    $result = $manager->remember('test.key', now()->addMinutes(60), fn () => 'original-value');

    expect($result)->toBe('original-value');
});

it('forgets cache value', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('test.key');

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    $manager->forget('test.key');
});

it('returns enabled status', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    expect($manager->isEnabled())->toBeTrue();
});

it('returns ttl', function () {
    $config = CacheConfiguration::fromArray(['ttl' => 120]);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    expect($manager->getTtl())->toBe(120);
});

it('generates cache key', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'custom']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config,
        $keyGenerator
    );

    $key = $manager->generateKey('App\\Models\\User', 123);

    expect($key)->toBe('custom.user.123');
});
