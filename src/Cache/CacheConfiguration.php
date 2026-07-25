<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;

class CacheConfiguration implements CacheConfigurationInterface
{
    public function __construct(
        public readonly bool $enabled,
        public readonly int $ttl,
        public readonly string $prefix,
        public readonly ?string $store,
    ) {}

    public static function fromArray(array $config): self
    {
        $ttl = new CacheTtl((int) ($config['ttl'] ?? 60));

        return new self(
            enabled: (bool) ($config['enabled'] ?? true),
            ttl: $ttl->value(),
            prefix: (string) ($config['prefix'] ?? 'auth'),
            store: $config['store'] ?? null,
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getStore(): ?string
    {
        return $this->store;
    }
}
