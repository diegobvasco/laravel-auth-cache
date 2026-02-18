<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithObserver;
use Illuminate\Support\Facades\Cache;

it('clears cache when model is updated', function () {
    Event::fake([
        CacheInvalidationRequested::class,
    ]);

    $user = UserWithObserver::factory()->create();

    $user->update(['name' => 'Updated Name']);

    Event::assertDispatchedOnce(CacheInvalidationRequested::class);
});

it('clears cache when model is deleted', function () {
    Event::fake([
        CacheInvalidationRequested::class,
    ]);

    $user = UserWithObserver::factory()->create();

    $user->delete();

    Event::assertDispatchedOnce(CacheInvalidationRequested::class);
});
