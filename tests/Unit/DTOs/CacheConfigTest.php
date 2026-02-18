<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\DTOs\CacheConfig;

it('creates cache config from array', function () {
    $config = CacheConfig::fromArray([
        'enabled' => true,
        'ttl' => 120,
        'prefix' => 'custom',
        'store' => 'redis',
    ]);

    expect($config->enabled)->toBeTrue();
    expect($config->ttl)->toBe(120);
    expect($config->prefix)->toBe('custom');
    expect($config->store)->toBe('redis');
});

it('uses defaults when values not provided', function () {
    $config = CacheConfig::fromArray([]);

    expect($config->enabled)->toBeTrue();
    expect($config->ttl)->toBe(60);
    expect($config->prefix)->toBe('auth');
    expect($config->store)->toBeNull();
});

it('creates new instance with modified enabled', function () {
    $config = CacheConfig::fromArray(['enabled' => true]);
    $newConfig = $config->withEnabled(false);

    expect($config->enabled)->toBeTrue();
    expect($newConfig->enabled)->toBeFalse();
    expect($newConfig->ttl)->toBe($config->ttl);
    expect($newConfig->prefix)->toBe($config->prefix);
});

it('creates new instance with modified ttl', function () {
    $config = CacheConfig::fromArray(['ttl' => 60]);
    $newConfig = $config->withTtl(120);

    expect($config->ttl)->toBe(60);
    expect($newConfig->ttl)->toBe(120);
    expect($newConfig->enabled)->toBe($config->enabled);
});

it('creates new instance with modified prefix', function () {
    $config = CacheConfig::fromArray(['prefix' => 'auth']);
    $newConfig = $config->withPrefix('custom');

    expect($config->prefix)->toBe('auth');
    expect($newConfig->prefix)->toBe('custom');
});

it('creates new instance with modified store', function () {
    $config = CacheConfig::fromArray(['store' => null]);
    $newConfig = $config->withStore('redis');

    expect($config->store)->toBeNull();
    expect($newConfig->store)->toBe('redis');
});
