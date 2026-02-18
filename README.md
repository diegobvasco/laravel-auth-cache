# This is my package laravel-auth-cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/diegobvasco/laravel-auth-cache.svg?style=flat-square)](https://packagist.org/packages/diegobvasco/laravel-auth-cache)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/diegobvasco/laravel-auth-cache/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/diegobvasco/laravel-auth-cache/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/diegobvasco/laravel-auth-cache/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/diegobvasco/laravel-auth-cache/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/diegobvasco/laravel-auth-cache.svg?style=flat-square)](https://packagist.org/packages/diegobvasco/laravel-auth-cache)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require diegobvasco/laravel-auth-cache
```


You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-auth-cache-config"
```

This is the contents of the published config file:

```php
return [
    'cache' => [
        'enabled' => (bool) env('AUTH_CACHE_ENABLED', true),
        'ttl' => (int) env('AUTH_CACHE_TTL', 60),
        'store' => env('AUTH_CACHE_STORE'),
        'prefix' => env('AUTH_CACHE_PREFIX', 'auth'),
    ],
];
```

## Usage

### 1. Configure the Auth Provider

Update your `config/auth.php` file to use the `cachedEloquent` provider:

```php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'cachedEloquent',
            'model' => App\Models\User::class,
        ],
    ],
];
```

### 2. Configure Cache Settings

Publish the config file and adjust the cache settings in `config/essentials.php`:

```php
return [
    'auth' => [
        'cache' => [
            'enabled' => env('AUTH_CACHE_ENABLED', true),
            'ttl' => env('AUTH_CACHE_TTL', 60),
            'store' => env('AUTH_CACHE_STORE', null),
            'prefix' => env('AUTH_CACHE_PREFIX', 'auth'),
        ],
    ],
],
```

#### Configuration Options

- **enabled**: Enable or disable caching (default: `true`)
- **ttl**: Cache time-to-live in minutes (default: `60`)
- **store**: Specific cache store to use (default: `null` - uses default cache store)
- **prefix**: Cache key prefix (default: `'auth'`)

### 3. Clearing cache

Both the trait and the observer clear the cache only listen Eloquent model events `updated` and `deleted`.

#### Using trait
Add the `HasCachedAuthProvider` trait to your user model:

```php
use DiegoVasconcelos\AuthCache\Concerns\Models\HasCachedAuthProvider;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasCachedAuthProvider;

    // ...
}
```

#### Using observer

Register the observer `CachedAuthObserver` to your user model:

```php
use DiegoVasconcelos\AuthCache\Observers\CachedAuthObserver;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(CachedAuthObserver::class)]
class User extends Authenticatable
{
    // ...
}
```

The example use `ObservedBy` to register the observer but you can use traditional way in `AppServiceProvider`. 

### Per-Guard Configuration

You can override cache settings per-guard in `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
        'cache' => [
            'ttl' => 120, // Override to 2 minutes for web
        ],
    ],
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
        'cache' => [
            'enabled' => false, // Disable cache for API
        ],
    ],
],
```

### Custom Cache Store

Use a specific cache store for auth caching by setting the `AUTH_CACHE_STORE` environment variable:

```env
CACHE_DRIVER=redis
AUTH_CACHE_STORE=redis
```

### How It Works

1. **Caching**: When a user is retrieved via `retrieveById()`, the provider checks if the model uses the `HasCachedAuthProvider` trait. If enabled, it caches the user record.
2. **Cache Key**: Cache keys are generated as `{prefix}.{model_basename}.{id}` (e.g., `auth.user.123`).
3. **Automatic Invalidation**: The cache is automatically cleared when the model is updated or deleted, ensuring stale data is not served.
4. **Configurable**: You can enable/disable caching, adjust TTL, use different cache stores, and customize the cache key prefix.

You can publish the config file with:

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Diego Vasconcelos](https://github.com/diegobvasco)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
