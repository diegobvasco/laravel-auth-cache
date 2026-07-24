<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
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
