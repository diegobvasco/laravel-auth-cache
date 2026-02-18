<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests\Fixtures\Models;

use DiegoVasconcelos\AuthCache\Observers\CachedAuthObserver;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\database\factories\UserWithObserverFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(CachedAuthObserver::class)]
class UserWithObserver extends User
{
    protected static string $factory = UserWithObserverFactory::class;
}
