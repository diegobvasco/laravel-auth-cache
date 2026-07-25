<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache\Contracts;

interface CacheKeyGeneratorInterface
{
    public function generate(string $modelClass, mixed $identifier): string;
}
