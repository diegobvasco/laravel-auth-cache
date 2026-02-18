<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests\Fixtures\database\factories;

use DiegoVasconcelos\AuthCache\Tests\Fixtures\Models\UserWithTrait;

class UserWithTraitFactory extends UserFactory
{
    protected $model = UserWithTrait::class;
}
