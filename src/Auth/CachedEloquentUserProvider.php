<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\DTOs\CachedUserData;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CachedEloquentUserProvider extends EloquentUserProvider
{
    public function __construct(
        $hasher,
        $model,
        private CacheManager $cacheManager,
        private CacheInvalidatorInterface $cacheInvalidator
    ) {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier): string|(Model&Authenticatable)|null
    {
        $cachedData = $this->cacheManager->remember(
            key: $this->cacheManager->generateKey($this->getModel(), $identifier),
            ttl: now()->addMinutes($this->cacheManager->getTtl()),
            callback: function () use ($identifier) {
                $result = parent::retrieveById($identifier);

                return CachedUserData::from($result);
            }
        );

        if (! $cachedData instanceof CachedUserData) {
            return $cachedData;
        }

        return $cachedData->toAuthenticatable();
    }

    public function removeCache($model, $identifier): void
    {
        $this->cacheInvalidator->invalidate($model, $identifier);
    }
}
