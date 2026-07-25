<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheManager;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInterface;
use Illuminate\Contracts\Cache\Repository;

it('implements cache interface', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config
    );

    expect($manager)->toBeInstanceOf(CacheInterface::class);
});

it('caches value when enabled', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('remember')
        ->once()
        ->with('test.key', Mockery::type(DateTimeInterface::class), Mockery::type('callable'))
        ->andReturn('cached-value');

    $manager = new CacheManager(
        $cacheMock,
        $config
    );

    $result = $manager->remember('test.key', now()->addMinutes(60), fn () => 'original-value');

    expect($result)->toBe('cached-value');
});

it('does not cache when disabled', function () {
    $config = CacheConfiguration::fromArray(['enabled' => false, 'ttl' => 60]);

    $cacheMock = Mockery::mock(Repository::class);

    $manager = new CacheManager(
        $cacheMock,
        $config
    );

    $result = $manager->remember('test.key', now()->addMinutes(60), fn () => 'original-value');

    expect($result)->toBe('original-value');
});

it('forgets cache value', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60]);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('test.key');

    $manager = new CacheManager(
        $cacheMock,
        $config
    );

    $manager->forget('test.key');
});
