<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\ValueObjects\CacheKey;

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

it('checks if two cache keys are equal', function () {
    $key1 = new CacheKey('auth.user.123');
    $key2 = new CacheKey('auth.user.123');
    $key3 = new CacheKey('auth.user.456');

    expect($key1->equals($key2))->toBeTrue();
    expect($key1->equals($key3))->toBeFalse();
});
