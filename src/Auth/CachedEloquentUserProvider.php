<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\Cache\Exceptions\CorruptedCacheEntry;
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
        private CacheConfigurationInterface $configuration,
    ) {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier): (Model&Authenticatable)|null
    {
        $key = $this->keyGenerator->generate($this->getModel(), $identifier);

        $cached = $this->cache->get($key);

        if (is_array($cached)) {
            try {
                return CachedUserData::fromArray($cached)->toAuthenticatable();
            } catch (CorruptedCacheEntry) {
                // Stale payload (e.g. a renamed model class or a legacy shape):
                // treat it as a miss and rebuild from the database below.
            }
        }

        $result = parent::retrieveById($identifier);

        if ($result === null) {
            return null;
        }

        $this->cache->put(
            key: $key,
            value: CachedUserData::fromModel($result)->toArray(),
            expiresAt: now()->addMinutes($this->configuration->getTtl()),
        );

        return $result;
    }
}
