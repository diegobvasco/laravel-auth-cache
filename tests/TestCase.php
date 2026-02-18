<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Tests;

use DiegoVasconcelos\AuthCache\AuthCacheServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Fixtures/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AuthCacheServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
