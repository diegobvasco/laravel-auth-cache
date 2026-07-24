<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache;

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Auth\CacheManager;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use DiegoVasconcelos\AuthCache\Listeners\InvalidateCacheListener;
use DiegoVasconcelos\AuthCache\Providers\CachedEloquentUserProviderRegistrar;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuthCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();

        $this->publishes([
            __DIR__.'/../config/auth-cache.php' => config_path('auth-cache.php'),
        ], 'laravel-auth-cache-config');

        $this->registerProvider();
        $this->bindCacheConfiguration();
        $this->bindCacheKeyGenerator();
        $this->bindCacheManager();
        $this->bindCacheInvalidator();
    }

    public function boot(): void
    {
        $this->registerEventListeners();
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-cache.php', 'auth-cache');
    }

    protected function registerProvider(): void
    {
        Auth::provider('cachedEloquent', function (Application $app, array $config) {
            $registrar = new CachedEloquentUserProviderRegistrar();

            return $registrar($app, $config);
        });
    }

    private function bindCacheConfiguration(): void
    {
        $this->app->singleton(CacheConfigurationInterface::class, function () {
            return CacheConfiguration::fromArray(config('auth-cache.cache', []));
        });
    }

    private function bindCacheKeyGenerator(): void
    {
        $this->app->bind(CacheKeyGeneratorInterface::class, function ($app, array $params = []) {
            return new CacheKeyGenerator(
                $params['configuration'] ?? $app->make(CacheConfigurationInterface::class)
            );
        });
    }

    private function bindCacheManager(): void
    {
        $this->app->bind(CacheInterface::class, function ($app, array $params = []) {
            return new CacheManager(
                cache: $params['cache'] ?? Cache::store(),
                configuration: $params['configuration'] ?? $app->make(CacheConfigurationInterface::class),
            );
        });
    }

    private function bindCacheInvalidator(): void
    {
        $this->app->bind(CacheInvalidatorInterface::class, function ($app, array $params = []) {
            $configuration = $params['configuration'] ?? $app->make(CacheConfigurationInterface::class);

            return new CacheInvalidator(
                cache: $params['cache'] ?? Cache::store(),
                keyGenerator: $app->make(CacheKeyGeneratorInterface::class, [
                    'configuration' => $configuration,
                ]),
            );
        });
    }

    private function registerEventListeners(): void
    {
        Event::listen(
            events: CacheInvalidationRequested::class,
            listener: InvalidateCacheListener::class
        );
    }
}
