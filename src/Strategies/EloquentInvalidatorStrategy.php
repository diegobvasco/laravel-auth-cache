<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Strategies;

use DiegoVasconcelos\AuthCache\Contracts\Cache\CacheInvalidatorInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentInvalidatorStrategy
{
    public function __construct(
        private CacheInvalidatorInterface $invalidator
    ) {}

    public function registerModel(string $modelClass): void
    {
        $invalidator = $this->invalidator;

        $modelClass::updated(function (Model $model) use ($invalidator) {
            $invalidator->invalidate($model, $model->getKey());
        });

        $modelClass::deleted(function (Model $model) use ($invalidator) {
            $invalidator->invalidate($model, $model->getKey());
        });
    }
}
