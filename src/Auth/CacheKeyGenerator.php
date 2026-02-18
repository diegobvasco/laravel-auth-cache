<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheKeyGeneratorInterface;
use DiegoVasconcelos\AuthCache\ValueObjects\CacheKey;

class CacheKeyGenerator implements CacheKeyGeneratorInterface
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
