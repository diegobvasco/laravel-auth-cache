<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;

readonly class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function __construct(
        private CacheConfigurationInterface $configuration
    ) {}

    public function generate(string $modelClass, mixed $identifier): string
    {
        return (string) new CacheKey(
            implode('.', [
                $this->configuration->getPrefix(),
                strtolower(class_basename($modelClass)),
                $identifier,
            ])
        );
    }
}
