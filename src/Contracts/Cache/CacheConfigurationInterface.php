<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Contracts\Cache;

interface CacheConfigurationInterface
{
    public function isEnabled(): bool;

    public function getTtl(): int;

    public function getPrefix(): string;

    public function getStore(): ?string;
}
