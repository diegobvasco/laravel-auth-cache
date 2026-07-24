<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\AuthCacheServiceProvider;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $configFile = config_path('auth-cache.php');
    if (file_exists($configFile)) {
        unlink($configFile);
    }
});

it('publishes the config file', function () {
    artisan('vendor:publish', [
        '--provider' => AuthCacheServiceProvider::class,
        '--tag' => 'laravel-auth-cache-config',
    ])->assertExitCode(0);

    expect(file_exists(config_path('auth-cache.php')))->toBeTrue();
})->group('publishes');

it('verify if auth provider is registered', function () {
    config()->set('auth.providers.cached_eloquent', [
        'driver' => 'cachedEloquent',
        'model' => User::class,
    ]);

    $provider = Auth::createUserProvider('cached_eloquent');

    expect($provider)->toBeInstanceOf(CachedEloquentUserProvider::class);
});

it('respects a custom bound cache key generator (container override)', function () {
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
        'model' => User::class,
    ]);

    $provider = Auth::createUserProvider('cached_eloquent');

    $user = User::factory()->create();

    $provider->retrieveById($user->id);

    expect(cache()->has($customKey))->toBeTrue();
});

it('throws when an unknown cache store is configured', function () {
    config()->set('auth-cache.cache.store', 'nonexistent_store');
    config()->set('auth.providers.cached_eloquent', [
        'driver' => 'cachedEloquent',
        'model' => User::class,
    ]);

    Auth::createUserProvider('cached_eloquent');
})->throws(InvalidArgumentException::class);
