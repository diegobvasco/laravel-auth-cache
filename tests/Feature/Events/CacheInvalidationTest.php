<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Cache\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Listeners\InvalidateCacheListener;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithTrait;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

it('clears cache on the configured custom store end-to-end', function () {
    config()->set('cache.stores.secondary', ['driver' => 'array']);
    config()->set('auth-cache.cache.store', 'secondary');
    config()->set('auth.providers.secondary_users', [
        'driver' => 'cachedEloquent',
        'model' => UserWithTrait::class,
    ]);

    $provider = Auth::createUserProvider('secondary_users');
    $user = UserWithTrait::factory()->create();

    $provider->retrieveById($user->id);

    $key = 'auth.userwithtrait.'.$user->id;

    expect(Cache::store('secondary')->has($key))->toBeTrue();
    expect(Cache::store()->has($key))->toBeFalse();

    $user->update(['name' => 'Custom Store Changed']);

    expect(Cache::store('secondary')->has($key))->toBeFalse();
});

it('clears every guard-level prefix override when a model changes', function () {
    config()->set('auth.providers.web_users', [
        'driver' => 'cachedEloquent',
        'model' => UserWithTrait::class,
        'cache' => ['prefix' => 'web'],
    ]);
    config()->set('auth.providers.api_users', [
        'driver' => 'cachedEloquent',
        'model' => UserWithTrait::class,
        'cache' => ['prefix' => 'api'],
    ]);

    $web = Auth::createUserProvider('web_users');
    $api = Auth::createUserProvider('api_users');
    $user = UserWithTrait::factory()->create();

    $web->retrieveById($user->id);
    $api->retrieveById($user->id);

    expect(Cache::has('web.userwithtrait.'.$user->id))->toBeTrue();
    expect(Cache::has('api.userwithtrait.'.$user->id))->toBeTrue();

    $user->update(['name' => 'Multi Prefix Changed']);

    expect(Cache::has('web.userwithtrait.'.$user->id))->toBeFalse();
    expect(Cache::has('api.userwithtrait.'.$user->id))->toBeFalse();
});

it('falls back to the global config when no provider has been registered', function () {
    config()->set('auth.providers', [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ]);

    Cache::put('auth.user.424242', ['type' => 'null'], 60);

    event(new CacheInvalidationRequested(new User(), 424242, 'manual'));

    expect(Cache::has('auth.user.424242'))->toBeFalse();
});

it('invalidates a provider-specific store without resolving the provider first', function () {
    config()->set('cache.stores.secondary', ['driver' => 'array']);
    config()->set('auth-cache.cache.store', 'secondary');
    config()->set('auth.providers.users', [
        'driver' => 'cachedEloquent',
        'model' => UserWithTrait::class,
    ]);

    Cache::store('secondary')->put('auth.userwithtrait.9', 'value', 60);

    event(new CacheInvalidationRequested(new UserWithTrait(), 9, 'manual'));

    expect(Cache::store('secondary')->has('auth.userwithtrait.9'))->toBeFalse();
});

it('respects a custom bound key generator during invalidation', function () {
    $customKey = 'custom.resolved.key';

    app()->bind(CacheKeyGeneratorInterface::class, function () use ($customKey) {
        return new class($customKey) implements CacheKeyGeneratorInterface
        {
            public function __construct(private string $key) {}

            public function generate(string $modelClass, mixed $identifier): string
            {
                return $this->key;
            }
        };
    });

    config()->set('auth.providers.cached_eloquent', [
        'driver' => 'cachedEloquent',
        'model' => UserWithTrait::class,
    ]);

    $provider = Auth::createUserProvider('cached_eloquent');
    $user = UserWithTrait::factory()->create();

    $provider->retrieveById($user->id);

    expect(cache()->has($customKey))->toBeTrue();

    $user->update(['name' => 'Generator Changed']);

    expect(cache()->has($customKey))->toBeFalse();
});
