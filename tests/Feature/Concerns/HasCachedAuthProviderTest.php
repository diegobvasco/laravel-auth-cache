<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithTrait;
use Illuminate\Support\Facades\Cache;

it('clears auth cache when model is updated', function () {
    Cache::spy();

    $user = UserWithTrait::factory()->create();

    $cacheKey = CachedEloquentUserProvider::generateCacheKeyStatic($user, $user->getKey());

    $user->update(['name' => 'Updated Name']);

    Cache::shouldHaveReceived('forget')->with($cacheKey);

    $user->delete();

    Cache::shouldHaveReceived('forget')->with($cacheKey)->atLeast()->times(2);
});
