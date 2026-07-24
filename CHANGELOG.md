# Changelog

All notable changes to `laravel-auth-cache` will be documented in this file.

## 2.0.0 - Unreleased

### Breaking Changes (refactor)

- Removed `CachedEloquentUserProvider::removeCache()`. Cache invalidation is now handled exclusively through the `CacheInvalidationRequested` event.
- Removed the unused `EloquentInvalidatorStrategy` class (dead code, never wired).
- Standardized the invalidation identifier: `CachedAuthObserver` now dispatches using `$user->getKey()` to match the trait and the cache key generation.
- `CachedEloquentUserProvider` now depends on `CacheInterface` + `CacheKeyGeneratorInterface` (and its constructor signature changed) instead of the concrete `CacheManager`.
- `CachedEloquentUserProviderRegistrar` now resolves collaborators from the container, so custom interface bindings (e.g. `CacheKeyGeneratorInterface`) are actually respected. Cache collaborators are registered as contextual bindings (`bind`) to support per-guard configuration.
- Slimmed `CacheInterface` to only cache operations (`remember`, `forget`). Removed `isEnabled()` and `getTtl()` from the interface (they belong to configuration). `CacheManager` no longer wraps a key generator or exposes `generateKey()`/`isEnabled()`/`getTtl()`.
- Consolidated all cache collaborators under the `DiegoVasconcelos\AuthCache\Cache` namespace (implementations, value objects, and `Cache\Contracts` interfaces). Removed the now-empty `Auth/` cache classes, `ValueObjects/`, and `Contracts/Cache/` locations.
- Folded the `CacheConfig` DTO into `CacheConfiguration` (the class now holds the validated fields directly). Removed `CacheConfig`, its `with*()` methods, and `CacheConfiguration::getConfig()`.
- Enforced the TTL invariant: `CacheConfiguration::fromArray()` now validates the TTL through the `CacheTtl` value object, so out-of-range values (below 1 or above 525600 minutes) throw at boot instead of being silently accepted.
- An unknown `AUTH_CACHE_STORE` now throws instead of silently falling back to the array store, surfacing misconfiguration immediately.
- Documentation fixes: corrected the `config/auth-cache.php` nesting, moved per-guard cache overrides to the provider config (where they are actually read), and updated customization examples to the new `Cache\Contracts` namespaces.

## v1.1.0 - 2026-06-26

### What's Changed

* Fix laravel 13 compatibility model serialize  by @diegobvasco in https://github.com/diegobvasco/laravel-auth-cache/pull/10

**Full Changelog**: https://github.com/diegobvasco/laravel-auth-cache/compare/1.0.3...1.1.0

## V1.0.2 - 2026-03-19

### What's Changed

* Add support for Laravel 13 in composer.json and update test matrix by @diegobvasco in https://github.com/diegobvasco/laravel-auth-cache/pull/3

**Full Changelog**: https://github.com/diegobvasco/laravel-auth-cache/compare/1.0.1...1.0.2

## 1.0.1 - 2026-02-18

### What's Changed

* Fix trait has cached auth provider by @diegobvasco in https://github.com/diegobvasco/laravel-auth-cache/pull/1

### New Contributors

* @diegobvasco made their first contribution in https://github.com/diegobvasco/laravel-auth-cache/pull/1

**Full Changelog**: https://github.com/diegobvasco/laravel-auth-cache/compare/1.0.0...1.0.1

## 1.0.0 - 2026-02-18

### Added

- **Interface-based architecture** for dependency injection and testability
  
  - `CacheInterface` - Core cache operations (remember, forget, isEnabled)
  - `CacheKeyGeneratorInterface` - Cache key generation strategy
  - `CacheInvalidatorInterface` - Cache invalidation handling
  - `CacheConfigurationInterface` - Configuration access abstraction
  
- **Value objects** for type safety
  
  - `CacheKey` - Validates cache key format
  - `CacheTtl` - Validates time-to-live values
  
- `CacheConfig` DTO - Immutable configuration object
  
- **Event-driven cache invalidation system**
  
  - `CacheInvalidationRequested` event dispatched on model update/delete
  - `InvalidateCacheListener` for automatic cache clearing
  
- **Service layer components**
  
  - `CacheConfiguration` - Configuration implementation
  - `CacheKeyGenerator` - Default key generation strategy
  - `CacheInvalidator` - Default cache invalidation logic
  
- `EloquentInvalidatorStrategy` - Dynamic model registration
  
- **Service providers**
  
  - `CacheServiceProvider` - Binds all cache services
  - `CachedEloquentUserProviderRegistrar` - Provider registration
  
- **Comprehensive test suite** (53 tests: 42 unit + 11 integration)
  
- **Documentation** - Architecture, events, and customization examples
  
- `HasCachedAuthProvider` trait - Automatic cache invalidation via events
  
- `CachedAuthObserver` - Event-based cache clearing
  
- `CachedEloquentUserProvider` - Laravel auth provider with caching support
  
- Per-guard configuration support
  
- Configurable cache store, TTL, and prefix
  
