<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->bindCacheConfiguration();
        $this->bindCacheKeyGenerator();
        $this->bindCacheManager();
        $this->bindCacheInvalidator();
    }

    public function boot(): void
    {
        $this->registerEventListeners();
    }

    private function bindCacheConfiguration(): void
    {
        $this->app->singleton(CacheConfigurationInterface::class, function () {
            return CacheConfiguration::fromArray(config('auth-cache.cache', []));
        });
    }

    private function bindCacheKeyGenerator(): void
    {
        $this->app->singleton(CacheKeyGeneratorInterface::class, function ($app) {
            return new CacheKeyGenerator(
                $app->make(CacheConfigurationInterface::class)
            );
        });
    }

    private function bindCacheManager(): void
    {
        $this->app->singleton(CacheInterface::class, function ($app) {
            return new CacheManager(
                cache: Cache::store(),
                configuration: $app->make(CacheConfigurationInterface::class),
                keyGenerator: $app->make(CacheKeyGeneratorInterface::class)
            );
        });
    }

    private function bindCacheInvalidator(): void
    {
        $this->app->singleton(CacheInvalidatorInterface::class, function ($app) {
            return new CacheInvalidator(
                cache: Cache::store(),
                keyGenerator: $app->make(CacheKeyGeneratorInterface::class)
            );
        });
    }

    private function registerEventListeners(): void
    {
        $this->app->get('events')->listen(
            CacheInvalidationRequested::class,
            InvalidateCacheListener::class
        );
    }
}
