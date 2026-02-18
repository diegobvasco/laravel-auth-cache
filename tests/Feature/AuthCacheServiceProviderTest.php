<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\AuthCacheServiceProvider;
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

    expect($provider)->toBeInstanceOf(\DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider::class);
});
