<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheConfigurationInterface;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::clearResolvedInstances();
});

it('implements cache configuration interface', function () {
    $config = CacheConfiguration::fromArray([]);

    expect($config)->toBeInstanceOf(CacheConfigurationInterface::class);
});

it('returns enabled status from config', function () {
    $config = CacheConfiguration::fromArray(['enabled' => true]);

    expect($config->isEnabled())->toBeTrue();

    $config = CacheConfiguration::fromArray(['enabled' => false]);

    expect($config->isEnabled())->toBeFalse();
});

it('returns default enabled when not specified', function () {
    $config = CacheConfiguration::fromArray([]);

    expect($config->isEnabled())->toBeTrue();
});

it('returns ttl from config', function () {
    $config = CacheConfiguration::fromArray(['ttl' => 120]);

    expect($config->getTtl())->toBe(120);
});

it('returns default ttl when not specified', function () {
    $config = CacheConfiguration::fromArray([]);

    expect($config->getTtl())->toBe(60);
});

it('returns prefix from config', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'custom']);

    expect($config->getPrefix())->toBe('custom');
});

it('returns default prefix when not specified', function () {
    $config = CacheConfiguration::fromArray([]);

    expect($config->getPrefix())->toBe('auth');
});

it('returns store from config', function () {
    $config = CacheConfiguration::fromArray(['store' => 'redis']);

    expect($config->getStore())->toBe('redis');
});

it('returns null store when not specified', function () {
    $config = CacheConfiguration::fromArray([]);

    expect($config->getStore())->toBeNull();
});

it('returns the underlying cache config', function () {
    $arrayConfig = ['enabled' => true, 'ttl' => 90, 'prefix' => 'test', 'store' => 'memcached'];
    $config = CacheConfiguration::fromArray($arrayConfig);

    expect($config->getConfig()->enabled)->toBeTrue();
    expect($config->getConfig()->ttl)->toBe(90);
    expect($config->getConfig()->prefix)->toBe('test');
    expect($config->getConfig()->store)->toBe('memcached');
});
