# Changelog

All notable changes to `laravel-auth-cache` will be documented in this file.

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
  
