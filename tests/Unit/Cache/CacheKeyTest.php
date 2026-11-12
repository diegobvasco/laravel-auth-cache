<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheKey;

it('creates a valid cache key', function () {
    $key = new CacheKey('auth.user.123');

    expect($key->value())->toBe('auth.user.123');
    expect((string) $key)->toBe('auth.user.123');
});

it('throws exception for empty cache key', function () {
    expect(fn () => new CacheKey(''))
        ->toThrow(InvalidArgumentException::class, 'Cache key cannot be empty');
});

it('throws exception for whitespace cache key', function () {
    expect(fn () => new CacheKey('   '))
        ->toThrow(InvalidArgumentException::class, 'Cache key cannot be empty');
});

it('throws exception for cache key with spaces', function () {
    expect(fn () => new CacheKey('auth user 123'))
        ->toThrow(InvalidArgumentException::class, 'Cache key cannot contain spaces');
});

it('accepts zero as a valid key value', function () {
    $key = new CacheKey('0');

    expect($key->value())->toBe('0');
});

it('throws exception for reserved characters', function () {
    foreach (['{', '}', '(', ')', '/', '\\', '@', ':'] as $reserved) {
        expect(fn () => new CacheKey("auth{$reserved}user"))
            ->toThrow(InvalidArgumentException::class, 'Cache key cannot contain reserved characters');
    }
});
