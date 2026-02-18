<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
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
        return $this->cacheManager->remember(
            key: $this->cacheManager->generateKey($this->getModel(), $identifier),
            ttl: now()->addMinutes($this->cacheManager->getTtl()),
            callback: fn () => parent::retrieveById($identifier)
        );
    }

    public function removeCache($model, $identifier): void
    {
        $this->cacheInvalidator->invalidate($model, $identifier);
    }
}
