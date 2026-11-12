<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache\Contracts;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value, \DateTimeInterface $expiresAt): bool;

    public function forget(string $key): void;
}
