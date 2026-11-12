<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Providers;

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use DiegoVasconcelos\AuthCache\Cache\CacheConfiguration;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Cache\ProviderCacheRegistry;
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

        $app->make(ProviderCacheRegistry::class)->register($configuration);

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

    public static function mergeCacheConfigs(array $config): array
    {
        $globalConfig = config('auth-cache.cache', []);

        $guardConfig = $config['cache'] ?? [];

        return array_merge($globalConfig, $guardConfig);
    }

    /**
     * Derive the cache configurations of every `cachedEloquent` provider
     * declared in `config/auth.php`, so invalidation works even in processes
     * where the providers themselves were never resolved (e.g. queue workers).
     *
     * @return list<CacheConfigurationInterface>
     */
    public static function configurationsFromAuthProviderConfig(): array
    {
        $configurations = [];

        foreach ((array) config('auth.providers', []) as $providerConfig) {
            if (! is_array($providerConfig) || ($providerConfig['driver'] ?? null) !== 'cachedEloquent') {
                continue;
            }

            $configurations[] = CacheConfiguration::fromArray(self::mergeCacheConfigs($providerConfig));
        }

        return $configurations;
    }

    private function getCacheRepository(?string $store): Repository
    {
        if ($store === null) {
            return Cache::store();
        }

        // Fail fast: an unknown store indicates misconfiguration.
        return Cache::store($store);
    }
}
