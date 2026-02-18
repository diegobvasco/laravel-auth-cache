<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Cache\Repository;

it('implements cache invalidator interface', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);

    $invalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    expect($invalidator)->toBeInstanceOf(CacheInvalidatorInterface::class);
});

it('invalidates cache for model', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('auth.user.123');

    $invalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    $user = new User();
    $user->id = 123;

    $invalidator->invalidate($user, 123);
});

it('invalidates cache with string identifier', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('auth.user.user_abc');

    $invalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    $user = new User();

    $invalidator->invalidate($user, 'user_abc');
});

it('uses model class name for key generation', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'custom']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('custom.user.456');

    $invalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    $user = new User();
    $user->id = 456;

    $invalidator->invalidate($user, 456);
});
