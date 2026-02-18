# Laravel Auth Cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/diegobvasco/laravel-auth-cache.svg?style=flat-square)](https://packagist.org/packages/diegobvasco/laravel-auth-cache)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/diegobvasco/laravel-auth-cache/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/diegobvasco/laravel-auth-cache/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/diegobvasco/laravel-auth-cache/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/diegobvasco/laravel-auth-cache/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/diegobvasco/laravel-auth-cache.svg?style=flat-square)](https://packagist.org/packages/diegobvasco/laravel-auth-cache)

A Laravel authentication caching package that provides optimized user retrieval with automatic cache invalidation. Built with SOLID principles, dependency injection, and event-driven architecture for maintainable and testable code.

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

Publish the config file and adjust the cache settings in `config/auth-cache.php`:

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
use DiegoVasconcelos\AuthCache\Concerns\HasCachedAuthProvider;
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

1. **Caching**: When a user is retrieved via `retrieveById()`, the provider caches the user record with the configured TTL.
2. **Cache Key**: Cache keys are generated as `{prefix}.{model_basename}.{id}` (e.g., `auth.user.123`).
3. **Automatic Invalidation**: When a model is updated or deleted, a `CacheInvalidationRequested` event is dispatched. The registered listener automatically clears the corresponding cache entry.
4. **Interface-Based Design**: All components use interfaces and dependency injection, making them testable and extensible.
5. **Configurable**: You can enable/disable caching, adjust TTL, use different cache stores, and customize the cache key prefix.

## Architecture

This package is built using SOLID principles and Laravel best practices:

### Interface-Based Design

All cache operations are defined by interfaces:
- **CacheInterface** - Core cache operations (remember, forget, isEnabled)
- **CacheKeyGeneratorInterface** - Strategy for generating cache keys
- **CacheInvalidatorInterface** - Handles cache invalidation
- **CacheConfigurationInterface** - Configuration access

### Dependency Injection

All components are resolved through Laravel's service container, providing:
- Easy testing with mocks
- Ability to replace implementations
- Loose coupling between components

### Event-Driven Cache Invalidation

- Events decouple cache clearing from models
- Listeners handle invalidation logic
- Multiple listeners can respond to cache events

## Events

### CacheInvalidationRequested

Dispatched when cache should be invalidated (on model update, delete, or manual request).

**Event Properties:**
- `model` - The model instance being invalidated
- `identifier` - The model's identifier (ID)
- `reason` - Why invalidation occurred: `updated`, `deleted`, or `manual`

**Example: Listen to Invalidation Events**
```php
use DiegoVasconcelos\AuthCache\Events\CacheInvalidationRequested;

Event::listen(CacheInvalidationRequested::class, function ($event) {
    Log::info("Cache cleared for {$event->reason}: {$event->identifier}");
});
```

## Extending

### Custom Cache Key Generator

Create your own key generation strategy:

```php
use DiegoVasconcelos\AuthCache\Contracts\CacheKeyGeneratorInterface;

class CustomKeyGenerator implements CacheKeyGeneratorInterface
{
    public function generate(string $modelClass, mixed $identifier): string
    {
        return "custom.{$modelClass}.{$identifier}";
    }
}

// Register in AppServiceProvider
app()->bind(
    CacheKeyGeneratorInterface::class,
    CustomKeyGenerator::class
);
```

### Custom Cache Invalidation

Implement your own invalidation logic:

```php
use DiegoVasconcelos\AuthCache\Contracts\CacheInvalidatorInterface;

class CustomInvalidator implements CacheInvalidatorInterface
{
    public function invalidate(object $model, mixed $identifier): void
    {
        // Your custom logic (e.g., log, broadcast, etc.)
        Cache::forget("custom.key.{$identifier}");
    }
}

app()->bind(
    CacheInvalidatorInterface::class,
    CustomInvalidator::class
);
```

### Custom Configuration

Modify configuration at runtime:

```php
use DiegoVasconcelos\AuthCache\Contracts\CacheConfigurationInterface;

app()->afterResolving(CacheConfigurationInterface::class, function ($config) {
    // Customize based on environment, user roles, etc.
});
```

## Testing

```bash
composer test
```

The package includes comprehensive tests:
- Unit tests for all components
- Integration tests for provider, trait, and observer
- Architecture tests ensuring code quality

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
