<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Concerns;

use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;
use Illuminate\Database\Eloquent\Model;

trait HasCachedAuthProvider
{
    protected static function bootHasCachedAuthProvider(): void
    {
        static::updated(function (Model $model) {
            CacheInvalidationRequested::dispatch($model, $model->getKey(), 'updated');
        });

        static::deleted(function (Model $model) {
            CacheInvalidationRequested::dispatch($model, $model->getKey(), 'deleted');
        });
    }
}
