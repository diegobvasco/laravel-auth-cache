<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;

/**
 * Collects every cache configuration the package builds for auth providers,
 * so invalidation can target the exact stores and key prefixes those
 * providers read and write from.
 */
class ProviderCacheRegistry
{
    /**
     * @var array<string, CacheConfigurationInterface>
     */
    private array $entries = [];

    public function register(CacheConfigurationInterface $configuration): void
    {
        if (! $configuration->isEnabled()) {
            return;
        }

        $this->entries[$this->fingerprint($configuration)] = $configuration;
    }

    /**
     * @return list<CacheConfigurationInterface>
     */
    public function entries(): array
    {
        return array_values($this->entries);
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    private function fingerprint(CacheConfigurationInterface $configuration): string
    {
        return $configuration->getStore().'|'.$configuration->getPrefix();
    }
}
