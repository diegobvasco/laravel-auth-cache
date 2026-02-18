<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Cache;

it('caches the user when model uses HasCachedAuthProvider', function () {
    Cache::spy();

    $user = User::factory()->create();

    $provider = new CachedEloquentUserProvider(
        app('hash'),
        User::class
    );

    $first = $provider->retrieveById($user->id);

    expect($first)->not->toBeNull();

    $cacheKey = CachedEloquentUserProvider::generateCacheKeyStatic(
        User::class,
        $user->id
    );

    expect(Cache::has($cacheKey))->toBeTrue();

    $second = $provider->retrieveById($user->id);

    expect($second->getKey())->toBe($user->getKey());
});
