<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests\Fixtures\Models;

use DiegoVasconcelos\AuthCache\Concerns\HasCachedAuthProvider;
use DiegoVasconcelos\AuthCache\Tests\Fixtures\database\factories\UserWithTraitFactory;

class UserWithTrait extends User
{
    use HasCachedAuthProvider;

    protected static string $factory = UserWithTraitFactory::class;
}
