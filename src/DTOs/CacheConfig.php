<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\DTOs;

readonly class CacheConfig
{
    public bool $enabled;

    public int $ttl;

    public string $prefix;

    public ?string $store;

    public function __construct(
        bool $enabled,
        int $ttl,
        string $prefix,
        ?string $store
    ) {
        $this->enabled = $enabled;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
        $this->store = $store;
    }

    public static function fromArray(array $config): self
    {
        return new self(
            enabled: (bool) ($config['enabled'] ?? true),
            ttl: (int) ($config['ttl'] ?? 60),
            prefix: (string) ($config['prefix'] ?? 'auth'),
            store: $config['store'] ?? null,
        );
    }

    public function withEnabled(bool $enabled): self
    {
        return new self(
            enabled: $enabled,
            ttl: $this->ttl,
            prefix: $this->prefix,
            store: $this->store,
        );
    }

    public function withTtl(int $ttl): self
    {
        return new self(
            enabled: $this->enabled,
            ttl: $ttl,
            prefix: $this->prefix,
            store: $this->store,
        );
    }

    public function withPrefix(string $prefix): self
    {
        return new self(
            enabled: $this->enabled,
            ttl: $this->ttl,
            prefix: $prefix,
            store: $this->store,
        );
    }

    public function withStore(?string $store): self
    {
        return new self(
            enabled: $this->enabled,
            ttl: $this->ttl,
            prefix: $this->prefix,
            store: $store,
        );
    }
}
