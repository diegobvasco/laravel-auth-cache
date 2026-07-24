<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Cache\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Listeners\InvalidateCacheListener;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Cache\Repository;

it('listener handles cache invalidation event', function () {
    Event::fake();
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    Event::assertListening(CacheInvalidationRequested::class, InvalidateCacheListener::class);

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
