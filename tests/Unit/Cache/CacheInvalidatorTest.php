<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Cache\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInvalidatorInterface;
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

    $invalidator->invalidate(User::class, 123);
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

    $invalidator->invalidate(User::class, 'user_abc');
});

it('generates the key from the given model class name', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'custom']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')->once()->with('custom.othermodel.456');

    $invalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    $invalidator->invalidate('App\\Fake\\OtherModel', 456);
});
