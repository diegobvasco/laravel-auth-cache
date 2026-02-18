<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use Illuminate\Contracts\Cache\Repository;

class CacheManager implements CacheInterface
{
    public function __construct(
        private Repository $cache,
        private CacheConfigurationInterface $configuration,
        private CacheKeyGeneratorInterface $keyGenerator
    ) {}

    public function remember(string $key, int|\DateTimeInterface $ttl, callable $callback): mixed
    {
        if (! $this->isEnabled()) {
            return $callback();
        }

        return $this->cache->remember($key, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }

    public function generateKey(string $model, mixed $identifier): string
    {
        return $this->keyGenerator->generate($model, $identifier);
    }

    public function isEnabled(): bool
    {
        return $this->configuration->isEnabled();
    }

    public function getTtl(): int
    {
        return $this->configuration->getTtl();
    }
}
