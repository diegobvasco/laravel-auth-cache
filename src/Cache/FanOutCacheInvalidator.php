<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\Cache;

use Closure;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheConfigurationInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheInvalidatorInterface;
use DiegoVasconcelos\AuthCache\Cache\Contracts\CacheKeyGeneratorInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates every cache location a `cachedEloquent` provider may have
 * written to: explicitly registered configurations (providers resolved
 * through the registrar) plus the ones derivable from `config/auth.php`.
 *
 * An unknown store fails fast here, matching the registrar behavior when
 * the provider itself is resolved.
 */
class FanOutCacheInvalidator implements CacheInvalidatorInterface
{
    /**
     * @param  Closure(CacheConfigurationInterface): CacheKeyGeneratorInterface  $keyGeneratorResolver
     * @param  Closure(): list<CacheConfigurationInterface>  $providerConfigurations
     */
    public function __construct(
        private ProviderCacheRegistry $registry,
        private CacheConfigurationInterface $fallbackConfiguration,
        private Closure $keyGeneratorResolver,
        private Closure $providerConfigurations,
    ) {}

    public function invalidate(object $model, mixed $identifier): void
    {
        $configurations = $this->resolveConfigurations();

        if ($configurations === []) {
            $configurations = [$this->fallbackConfiguration];
        }

        foreach ($configurations as $configuration) {
            $keyGenerator = ($this->keyGeneratorResolver)($configuration);

            $key = $keyGenerator->generate(get_class($model), $identifier);

            $this->repositoryFor($configuration)->forget($key);
        }
    }

    /**
     * @return list<CacheConfigurationInterface>
     */
    private function resolveConfigurations(): array
    {
        foreach (($this->providerConfigurations)() as $configuration) {
            $this->registry->register($configuration);
        }

        return $this->registry->entries();
    }

    private function repositoryFor(CacheConfigurationInterface $configuration): Repository
    {
        return $configuration->getStore() === null
            ? Cache::store()
            : Cache::store($configuration->getStore());
    }
}
