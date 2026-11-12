<?php

declare(strict_types=1);

use DiegoVasconcelos\AuthCache\Cache\CacheTtl;

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
