<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Contracts\Cache;

interface CacheKeyGeneratorInterface
{
    public function generate(string $modelClass, mixed $identifier): string;
}
