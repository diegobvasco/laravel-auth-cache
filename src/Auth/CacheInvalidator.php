<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use Illuminate\Contracts\Cache\Repository;

readonly class CacheInvalidator implements CacheInvalidatorInterface
{
    public function __construct(
        private Repository $cache,
        private CacheKeyGeneratorInterface $keyGenerator
    ) {}

    public function invalidate(object $model, mixed $identifier): void
    {
        $key = $this->keyGenerator->generate(get_class($model), $identifier);
        $this->cache->forget($key);
    }
}
