<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Listeners;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;

readonly class InvalidateCacheListener
{
    public function __construct(
        private CacheInvalidatorInterface $cacheInvalidator
    ) {}

    public function handle(CacheInvalidationRequested $event): void
    {
        $this->cacheInvalidator->invalidate($event->model, $event->identifier);
    }
}
