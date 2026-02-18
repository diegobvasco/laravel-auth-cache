<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithTrait;
use Illuminate\Support\Facades\Cache;

it('clears cache when model is updated', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $user = UserWithTrait::factory()->create();

    $cacheKey = $keyGenerator->generate(get_class($user), $user->getKey());

    Cache::put($cacheKey, 'cached-value', 60);

    expect(Cache::has($cacheKey))->toBeTrue();

    $user->update(['name' => 'Updated Name']);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('clears cache when model is deleted', function () {
    $config = CacheConfiguration::fromArray(['prefix' => 'auth']);
    $keyGenerator = new CacheKeyGenerator($config);

    $user = UserWithTrait::factory()->create();

    $cacheKey = $keyGenerator->generate(get_class($user), $user->getKey());

    Cache::put($cacheKey, 'cached-value', 60);

    expect(Cache::has($cacheKey))->toBeTrue();

    $user->delete();

    expect(Cache::has($cacheKey))->toBeFalse();
});
