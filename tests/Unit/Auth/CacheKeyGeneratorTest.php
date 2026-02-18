<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;

it('implements cache key generator interface', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $generator = new CacheKeyGenerator($config);

    expect($generator)->toBeInstanceOf(CacheKeyGeneratorInterface::class);
});

it('generates cache key with default prefix', function () {
    $config = CacheConfiguration::fromArray([]);
    $generator = new CacheKeyGenerator($config);

    $key = $generator->generate('App\\Models\\User', 123);

    expect($key)->toBe('auth.user.123');
});

it('generates cache key with custom prefix', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'custom']);
    $generator = new CacheKeyGenerator($config);

    $key = $generator->generate('App\\Models\\User', 456);

    expect($key)->toBe('custom.user.456');
});

it('lowercases model class name in key', function () {
    $config = CacheConfiguration::fromArray([]);
    $generator = new CacheKeyGenerator($config);

    $key = $generator->generate('App\\Models\\AdminUser', 789);

    expect($key)->toBe('auth.adminuser.789');
});

it('uses full model class name to get basename', function () {
    $config = CacheConfiguration::fromArray([]);
    $generator = new CacheKeyGenerator($config);

    $key = $generator->generate('App\\Domain\\Models\\UserProfile', 10);

    expect($key)->toBe('auth.userprofile.10');
});

it('handles string identifiers', function () {
    $config = CacheConfiguration::fromArray([]);
    $generator = new CacheKeyGenerator($config);

    $key = $generator->generate('App\\Models\\User', 'user_abc');

    expect($key)->toBe('auth.user.user_abc');
});
