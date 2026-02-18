<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\ValueObjects;

use InvalidArgumentException;

class CacheTtl
{
    private int $value;

    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(int $value): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Cache TTL must be at least 1 minute');
        }

        if ($value > 525600) {
            throw new InvalidArgumentException('Cache TTL cannot exceed 525600 minutes (1 year)');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
