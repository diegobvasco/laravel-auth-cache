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

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function put(string $key, mixed $value, \DateTimeInterface $expiresAt): bool
    {
        if (! $this->configuration->isEnabled()) {
            return false;
        }

        return $this->cache->put($key, $value, $expiresAt);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
