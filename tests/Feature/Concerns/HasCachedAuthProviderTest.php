<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithTrait;

it('clears cache when model is updated', function () {
    Event::fake([
        CacheInvalidationRequested::class,
    ]);

    $user = UserWithTrait::factory()->create();

    $user->update(['name' => 'Updated Name']);

    Event::assertDispatched(CacheInvalidationRequested::class, 1);
});

it('clears cache when model is deleted', function () {
    Event::fake([
        CacheInvalidationRequested::class,
    ]);

    $user = UserWithTrait::factory()->create();

    $user->delete();

    Event::assertDispatched(CacheInvalidationRequested::class, 1);
});
