<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Observers;

use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use Illuminate\Contracts\Auth\Authenticatable;

class CachedAuthObserver
{
    public function updated(Authenticatable $user): void
    {
        CacheInvalidationRequested::dispatch($user, $user->getAuthIdentifier(), 'updated');
    }

    public function deleted(Authenticatable $user): void
    {
        CacheInvalidationRequested::dispatch($user, $user->getAuthIdentifier(), 'deleted');
    }
}
