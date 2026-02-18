<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\ValueObjects;

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
        if (empty($value) || trim($value) === '') {
            throw new InvalidArgumentException('Cache key cannot be empty');
        }

        if (str_contains($value, ' ')) {
            throw new InvalidArgumentException('Cache key cannot contain spaces');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
