<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\DTOs\CachedUserData;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CachedEloquentUserProvider extends EloquentUserProvider
{
    public function __construct(
        $hasher,
        $model,
        private CacheInterface $cache,
        private CacheKeyGeneratorInterface $keyGenerator,
    ) {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier): string|(Model&Authenticatable)|null
    {
        $cachedData = $this->cache->remember(
            key: $this->keyGenerator->generate($this->getModel(), $identifier),
            ttl: now()->addMinutes($this->cache->getTtl()),
            callback: function () use ($identifier) {
                $result = parent::retrieveById($identifier);

                return CachedUserData::from($result)->toArray();
            }
        );

        if (! is_array($cachedData)) {
            return $cachedData;
        }

        return CachedUserData::fromArray($cachedData)->toAuthenticatable();
    }
}
