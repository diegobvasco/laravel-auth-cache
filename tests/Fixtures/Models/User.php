<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests\Fixtures\Models;

use DiegoVasconcelos\AuthCache\Tests\Fixtures\database\factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected static string $factory = UserFactory::class;

    protected $guarded = [];
}
