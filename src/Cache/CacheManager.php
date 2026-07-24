<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInterface;
use Illuminate\Contracts\Cache\Repository;

class CacheManager implements CacheInterface
{
    public function __construct(
        private Repository $cache,
        private CacheConfigurationInterface $configuration,
    ) {}

    public function remember(string $key, int|\DateTimeInterface $ttl, callable $callback): mixed
    {
        if (! $this->configuration->isEnabled()) {
            return $callback();
        }

        return $this->cache->remember($key, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
