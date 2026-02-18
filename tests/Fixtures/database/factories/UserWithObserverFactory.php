<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests\Fixtures\database\factories;

use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithObserver;

class UserWithObserverFactory extends UserFactory
{
    protected $model = UserWithObserver::class;
}
