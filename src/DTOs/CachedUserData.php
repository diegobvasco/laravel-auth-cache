<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\DTOs;

use DiegoVasconcelos\AuthCache\Cache\Exceptions\CorruptedCacheEntry;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

readonly class CachedUserData
{
    public ?string $modelClass;

    public ?array $attributes;

    public bool $exists;

    private function __construct(
        ?string $modelClass,
        ?array $attributes,
        bool $exists
    ) {
        $this->modelClass = $modelClass;
        $this->attributes = $attributes;
        $this->exists = $exists;
    }

    public static function fromModel(Model&Authenticatable $model): self
    {
        return new self(
            modelClass: $model::class,
            attributes: $model->getAttributes(),
            exists: $model->exists
        );
    }

    /**
     * Reconstruct the DTO from its plain array representation.
     *
     * Storing the cached data as a plain array of primitives guarantees that
     * the value can always be unserialized, regardless of the host
     * application's `cache.serializable_classes` security configuration.
     */
    public static function fromArray(array $data): self
    {
        if (! is_string($data['modelClass'] ?? null) || ! is_array($data['attributes'] ?? null)) {
            throw new CorruptedCacheEntry('Cached user payload is missing or has invalid fields.');
        }

        return new self(
            modelClass: $data['modelClass'],
            attributes: $data['attributes'],
            exists: (bool) ($data['exists'] ?? true),
        );
    }

    /**
     * Convert the DTO to a plain array of primitives safe to cache.
     */
    public function toArray(): array
    {
        return [
            'modelClass' => $this->modelClass,
            'attributes' => $this->attributes,
            'exists' => $this->exists,
        ];
    }

    public function toAuthenticatable(): Model&Authenticatable
    {
        if (! class_exists($this->modelClass)
            || ! is_subclass_of($this->modelClass, Model::class)
            || ! is_a($this->modelClass, Authenticatable::class, true)) {
            throw new CorruptedCacheEntry(
                "Cached model class [{$this->modelClass}] does not exist or is not an Eloquent authenticatable model."
            );
        }

        /** @var Model&Authenticatable $model */
        $model = new $this->modelClass();
        $model->setRawAttributes($this->attributes, true);
        $model->exists = $this->exists;

        return $model;
    }
}
