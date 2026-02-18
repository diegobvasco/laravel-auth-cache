<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Providers;

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Auth\CacheInvalidator;
use DiegoVasconcelos\AuthCache\Auth\CacheKeyGenerator;
use DiegoVasconcelos\AuthCache\Auth\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;

class CachedEloquentUserProviderRegistrar
{
    public function __invoke(Application $app, array $config): CachedEloquentUserProvider
    {
        $cacheConfig = $this->mergeCacheConfigs($config);

        $cacheRepository = $this->getCacheRepository($cacheConfig['store'] ?? null);

        $cacheConfiguration = CacheConfiguration::fromArray($cacheConfig);

        $cacheKeyGenerator = new CacheKeyGenerator($cacheConfiguration);

        $cacheManager = new CacheManager(
            cache: $cacheRepository,
            configuration: $cacheConfiguration,
            keyGenerator: $cacheKeyGenerator
        );

        $cacheInvalidator = new CacheInvalidator(
            cache: $cacheRepository,
            keyGenerator: $cacheKeyGenerator
        );

        return new CachedEloquentUserProvider(
            hasher: $app['hash'],
            model: $config['model'],
            cacheManager: $cacheManager,
            cacheInvalidator: $cacheInvalidator
        );
    }

    private function mergeCacheConfigs(array $config): array
    {
        $globalConfig = config('auth-cache.cache', []);

        $guardConfig = $config['cache'] ?? [];

        return array_merge($globalConfig, $guardConfig);
    }

    private function getCacheRepository(?string $store): Repository
    {
        try {
            return $store ? Cache::store($store) : Cache::store();
        } catch (\Throwable $e) {
            return Cache::store('array');
        }
    }
}
