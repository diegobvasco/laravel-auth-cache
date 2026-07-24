<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Providers;

use DiegoVasconcelos\AuthCache\Auth\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;

class CachedEloquentUserProviderRegistrar
{
    public function __invoke(Application $app, array $config): CachedEloquentUserProvider
    {
        $cacheConfig = $this->mergeCacheConfigs($config);

        $cacheRepository = $this->getCacheRepository($cacheConfig['store'] ?? null);

        $configuration = CacheConfiguration::fromArray($cacheConfig);

        $cacheManager = $app->make(CacheInterface::class, [
            'cache' => $cacheRepository,
            'configuration' => $configuration,
        ]);

        $keyGenerator = $app->make(CacheKeyGeneratorInterface::class, [
            'configuration' => $configuration,
        ]);

        return new CachedEloquentUserProvider(
            hasher: $app['hash'],
            model: $config['model'],
            cache: $cacheManager,
            keyGenerator: $keyGenerator,
            configuration: $configuration,
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
