<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CacheInvalidationRequested
{
    use Dispatchable;

    public function __construct(
        public object $model,
        public mixed $identifier,
        public string $reason = 'manual'
    ) {}
}
