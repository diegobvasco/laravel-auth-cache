<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache;

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();

        $this->publishes([
            __DIR__.'/../config/auth-cache.php' => config_path('auth-cache.php'),
        ], 'laravel-auth-cache-config');
    }

    public function boot(): void
    {
        $this->registerProvider();
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-cache.php', 'auth-cache');
    }

    protected function registerProvider(): void
    {
        Auth::provider('cachedEloquent', function (Application $app, array $config) {
            $globalConfig = config('essentials.auth.cache', []);

            $guardConfig = $config['cache'] ?? [];

            $cacheConfig = array_merge($globalConfig, $guardConfig);

            return new CachedEloquentUserProvider(
                hasher: $app['hash'],
                model: $config['model'],
                config: $cacheConfig
            );
        });
    }
}
