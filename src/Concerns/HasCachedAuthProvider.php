<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Concerns;

use DiegoVasconcelos\AuthCache\Auth\CachedEloquentUserProvider;
use Illuminate\Database\Eloquent\Model;

trait HasCachedAuthProvider
{
    protected function initializeHasCachedAuthProvider(): void
    {
        static::updated(function (Model $model) {
            CachedEloquentUserProvider::removeCacheStatic($model, $model->getKey());
        });

        static::deleted(function (Model $model) {
            CachedEloquentUserProvider::removeCacheStatic($model, $model->getKey());
        });
    }
}
