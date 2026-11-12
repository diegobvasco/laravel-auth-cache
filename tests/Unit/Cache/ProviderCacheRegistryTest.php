<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\ProviderCacheRegistry;

it('starts empty', function () {
    $registry = new ProviderCacheRegistry();

    expect($registry->isEmpty())->toBeTrue();
    expect($registry->entries())->toBe([]);
});

it('registers configurations', function () {
    $registry = new ProviderCacheRegistry();

    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth']));

    expect($registry->isEmpty())->toBeFalse();
    expect($registry->entries())->toHaveCount(1);
    expect($registry->entries()[0]->getPrefix())->toBe('auth');
});

it('skips disabled configurations', function () {
    $registry = new ProviderCacheRegistry();

    $registry->register(CacheConfiguration::fromArray(['enabled' => false, 'prefix' => 'auth']));

    expect($registry->isEmpty())->toBeTrue();
    expect($registry->entries())->toBe([]);
});

it('deduplicates entries with the same store and prefix', function () {
    $registry = new ProviderCacheRegistry();

    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth', 'ttl' => 60]));
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth', 'ttl' => 120]));

    expect($registry->entries())->toHaveCount(1);
});

it('keeps distinct stores and prefixes separate', function () {
    $registry = new ProviderCacheRegistry();

    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth']));
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'auth', 'store' => 'redis']));
    $registry->register(CacheConfiguration::fromArray(['prefix' => 'api']));

    expect($registry->entries())->toHaveCount(3);
});
