<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Listeners\InvalidateCacheListener;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Cache\Repository;

it('listener handles cache invalidation event', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $cacheMock = Mockery::mock(Repository::class);
    $cacheMock->shouldReceive('forget')
        ->once()
        ->with('auth.user.1');

    $cacheInvalidator = new CacheInvalidator(
        $cacheMock,
        $keyGenerator
    );

    $listener = new InvalidateCacheListener($cacheInvalidator);

    $event = new CacheInvalidationRequested(new User(), 1, 'updated');

    $listener->handle($event);
});
