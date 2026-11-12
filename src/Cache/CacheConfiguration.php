<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;

class CacheConfiguration implements CacheConfigurationInterface
{
    public function __construct(
        private readonly bool $enabled,
        private readonly int $ttl,
        private readonly string $prefix,
        private readonly ?string $store,
    ) {}

    public static function fromArray(array $config): self
    {
        $enabled = (bool) ($config['enabled'] ?? true);

        $ttl = $enabled
            ? new CacheTtl(filter_var($config['ttl'] ?? 60, FILTER_VALIDATE_INT, ['options' => ['default' => 60]]))->value()
            : 0;

        return new self(
            enabled: $enabled,
            ttl: $ttl,
            prefix: (string) ($config['prefix'] ?? 'auth'),
            store: is_string($config['store'] ?? null) && $config['store'] !== '' ? $config['store'] : null,
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
