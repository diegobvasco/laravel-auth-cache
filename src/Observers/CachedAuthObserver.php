<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Observers;

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CachedAuthObserver
{
    public function updated(Authenticatable $user): void
    {
        CachedEloquentUserProvider::removeCacheStatic($user, $user->getAuthIdentifier());
    }

    public function deleted(Authenticatable $user): void
    {
        CachedEloquentUserProvider::removeCacheStatic($user, $user->getAuthIdentifier());
    }
}
