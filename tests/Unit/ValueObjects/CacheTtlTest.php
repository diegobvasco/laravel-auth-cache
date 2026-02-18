<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\ValueObjects\CacheTtl;

it('creates a valid cache ttl', function () {
    $ttl = new CacheTtl(60);

    expect($ttl->value())->toBe(60);
    expect((string) $ttl)->toBe('60');
});

it('throws exception for ttl less than 1', function () {
    expect(fn () => new CacheTtl(0))
        ->toThrow(InvalidArgumentException::class, 'Cache TTL must be at least 1 minute');

    expect(fn () => new CacheTtl(-5))
        ->toThrow(InvalidArgumentException::class, 'Cache TTL must be at least 1 minute');
});

it('throws exception for ttl exceeding 1 year', function () {
    expect(fn () => new CacheTtl(525601))
        ->toThrow(InvalidArgumentException::class, 'Cache TTL cannot exceed 525600 minutes (1 year)');
});

it('checks if two ttls are equal', function () {
    $ttl1 = new CacheTtl(60);
    $ttl2 = new CacheTtl(60);
    $ttl3 = new CacheTtl(120);

    expect($ttl1->equals($ttl2))->toBeTrue();
    expect($ttl1->equals($ttl3))->toBeFalse();
});
