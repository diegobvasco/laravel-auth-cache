<?php

declare(strict_types=1);

namespace DiegoVasconcelos\AuthCache\DTOs;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

readonly class CachedUserData
{
    public string $type;

    public ?string $modelClass;

    public ?array $attributes;

    public ?string $value;

    public bool $exists;

    private function __construct(
        string $type,
        ?string $modelClass,
        ?array $attributes,
        ?string $value,
        bool $exists
    ) {
        $this->type = $type;
        $this->modelClass = $modelClass;
        $this->attributes = $attributes;
        $this->value = $value;
        $this->exists = $exists;
    }

    public static function fromModel(Model&Authenticatable $model): self
    {
        return new self(
            type: 'model',
            modelClass: $model::class,
            attributes: $model->getAttributes(),
            value: null,
            exists: $model->exists
        );
    }

    public static function fromString(string $value): self
    {
        return new self(
            type: 'string',
            modelClass: null,
            attributes: null,
            value: $value,
            exists: true
        );
    }

    public static function fromNull(): self
    {
        return new self(
            type: 'null',
            modelClass: null,
            attributes: null,
            value: null,
            exists: false
        );
    }

    public static function from(mixed $value): self
    {
        if ($value === null) {
            return self::fromNull();
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        if ($value instanceof Model && $value instanceof Authenticatable) {
            return self::fromModel($value);
        }

        throw new \InvalidArgumentException(
            'Unsupported value type for caching. Expected Model, string, or null.'
        );
    }

    public function toAuthenticatable(): string|(Model&Authenticatable)|null
    {
        return match ($this->type) {
            'null' => null,
            'string' => $this->value,
            'model' => $this->rehydrateModel(),
            default => throw new \RuntimeException("Unknown cached data type: {$this->type}")
        };
    }

    private function rehydrateModel(): Model&Authenticatable
    {
        if ($this->modelClass === null || $this->attributes === null) {
            throw new \RuntimeException('Model data is incomplete for rehydration');
        }

        $model = new $this->modelClass();
        $model->setRawAttributes($this->attributes, true);
        $model->exists = $this->exists;

        return $model;
    }
}
