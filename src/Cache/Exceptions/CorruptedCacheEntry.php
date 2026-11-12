<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache\Exceptions;

use RuntimeException;

/**
 * Thrown when a cached auth payload cannot be trusted (missing fields,
 * unknown model class). Callers must treat it as a cache miss.
 */
class CorruptedCacheEntry extends RuntimeException {}
