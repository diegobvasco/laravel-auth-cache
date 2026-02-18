<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Auth;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\DTOs\CacheConfig;

class CacheConfiguration implements CacheConfigurationInterface
{
    private CacheConfig $config;

    public function __construct(CacheConfig $config)
    {
        $this->config = $config;
    }

    public static function fromArray(array $config): self
    {
        return new self(CacheConfig::fromArray($config));
    }

    public function isEnabled(): bool
    {
        return $this->config->enabled;
    }

    public function getTtl(): int
    {
        return $this->config->ttl;
    }

    public function getPrefix(): string
    {
        return $this->config->prefix;
    }

    public function getStore(): ?string
    {
        return $this->config->store;
    }

    public function getConfig(): CacheConfig
    {
        return $this->config;
    }
}
