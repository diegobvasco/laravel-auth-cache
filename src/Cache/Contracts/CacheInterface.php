<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache\Contracts;

interface CacheInterface
{
    public function remember(string $key, int|\DateTimeInterface $ttl, callable $callback): mixed;

    public function forget(string $key): void;
}
