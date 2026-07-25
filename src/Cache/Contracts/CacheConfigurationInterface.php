<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache\Contracts;

interface CacheConfigurationInterface
{
    public function isEnabled(): bool;

    public function getTtl(): int;

    public function getPrefix(): string;

    public function getStore(): ?string;
}
