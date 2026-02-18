<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Contracts\Cache;

interface CacheInvalidatorInterface
{
    public function invalidate(object $model, mixed $identifier): void;
}
