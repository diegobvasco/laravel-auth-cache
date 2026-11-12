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

it('gets values from the repository', function () {
    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('get')
        ->once()
        ->with('test.key')
        ->andReturn('cached-value');

    $manager = new CacheManager(
        $cacheMock,
        CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60])
    );

    expect($manager->get('test.key'))->toBe('cached-value');
});

it('puts values when enabled', function () {
    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('put')
        ->once()
        ->with('test.key', 'value', Mockery::type(DateTimeInterface::class))
        ->andReturn(true);

    $manager = new CacheManager(
        $cacheMock,
        CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60])
    );

    expect($manager->put('test.key', 'value', now()->addMinutes(60)))->toBeTrue();
});

it('does not put values when disabled', function () {
    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldNotReceive('put');

    $manager = new CacheManager(
        $cacheMock,
        CacheConfiguration::fromArray(['enabled' => false, 'ttl' => 60])
    );

    expect($manager->put('test.key', 'value', now()->addMinutes(60)))->toBeFalse();
});

it('gets values through when disabled', function () {
    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('get')
        ->once()
        ->with('test.key')
        ->andReturn('cached-value');

    $manager = new CacheManager(
        $cacheMock,
        CacheConfiguration::fromArray(['enabled' => false, 'ttl' => 60])
    );

    expect($manager->get('test.key'))->toBe('cached-value');
});

it('forgets cache value', function () {
    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('test.key');

    $manager = new CacheManager(
        $cacheMock,
        CacheConfiguration::fromArray(['enabled' => true, 'ttl' => 60])
    );

    $manager->forget('test.key');
});
