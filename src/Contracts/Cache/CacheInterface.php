<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Contracts\Cache;

interface CacheInterface
{
    public function remember(string $key, int|\DateTimeInterface $ttl, callable $callback): mixed;

    public function forget(string $key): void;

    public function isEnabled(): bool;

    public function getTtl(): int;
}
