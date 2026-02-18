<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CachedEloquentUserProvider extends EloquentUserProvider
{
    protected ?array $config;

    public function __construct($hasher, $model, ?array $config = null)
    {
        parent::__construct($hasher, $model);

        $this->config = $config ?? config('auth-cache.cache', []);
    }

    public function retrieveById($identifier): string|(Model&Authenticatable)|null
    {
        if (! $this->isCacheEnabled()) {
            return parent::retrieveById($identifier);
        }

        return $this->getCacheStore()->remember(
            key: $this->generateCacheKey($this->getModel(), $identifier),
            ttl: now()->addMinutes($this->getCacheTtl()),
            callback: fn () => parent::retrieveById($identifier)
        );
    }

    public function removeCache($model, $identifier): void
    {
        $this->getCacheStore()->forget($this->generateCacheKey($model, $identifier));
    }

    public static function removeCacheStatic($model, $identifier): void
    {
        $provider = self::getProviderForModel($model);

        if ($provider) {
            $provider->removeCache($model, $identifier);
        } else {
            Cache::forget(self::generateCacheKeyStatic($model, $identifier));
        }
    }

    public function generateCacheKey($model, $identifier): string
    {
        return implode('.', [
            $this->getCachePrefix(),
            strtolower(class_basename($model)),
            $identifier,
        ]);
    }

    public static function generateCacheKeyStatic($model, $identifier): string
    {
        $prefix = config('essentials.auth.cache.prefix', 'auth');

        return implode('.', [
            $prefix,
            strtolower(class_basename($model)),
            $identifier,
        ]);
    }

    protected static function getProviderForModel($model): ?self
    {
        $guards = array_keys(config('auth.guards', []));

        foreach ($guards as $guard) {
            $guardConfig = config("auth.guards.{$guard}");

            if (! $guardConfig) {
                continue;
            }

            $providerName = $guardConfig['provider'] ?? null;

            if (! $providerName) {
                continue;
            }

            $provider = Auth::createUserProvider($providerName);

            if ($provider instanceof self && $provider->getModel() === get_class($model)) {
                return $provider;
            }
        }

        return null;
    }

    protected function isCacheEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    protected function getCacheTtl(): int
    {
        return (int) ($this->config['ttl'] ?? 60);
    }

    protected function getCacheStore()
    {
        $store = $this->config['store'] ?? null;

        $cacheStore = $store ? Cache::store($store) : Cache::store();

        if ($cacheStore === null) {
            return Cache::store('array');
        }

        return $cacheStore;
    }

    protected function getCachePrefix(): string
    {
        return $this->config['prefix'] ?? 'auth';
    }
}
