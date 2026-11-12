<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use InvalidArgumentException;

class CacheKey
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if ($value === '' || trim($value) === '') {
            throw new InvalidArgumentException('Cache key cannot be empty');
        }

        if (str_contains($value, ' ')) {
            throw new InvalidArgumentException('Cache key cannot contain spaces');
        }

        if (preg_match('#[\s{}()/\\\\@:]#', $value) === 1) {
            throw new InvalidArgumentException('Cache key cannot contain reserved characters');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
