<?php

declare(strict_types=1);

namespace Collectibles;

use Collectibles\Arrays;
use Collectibles\Contracts\Collection as CollectionContract;
use Generator;
use LogicException;
use RuntimeException;

class Collection implements CollectionContract
{
    private bool $recount = false;
    private int $size = 0;
    private array $values = [];
    private int $defaultKey = 0;

    /**
     * 
     * @param string|null $collectionType Use valid class names only, triggers class autoloader
     * @throws LogicException If class name is not valid
     */
    public function __construct(
        private readonly ?string $collectionType = null
    ) {
        $valid = null === $collectionType
            ?: (class_exists($collectionType, true) || interface_exists($collectionType, true));
        if (!$valid) {
            throw new LogicException('Use valid class names only');
        }
    }

    public function has(array|int|string $key): bool
    {
        return Arrays::hasKey($key, $this->values);
    }

    public function get(
        array|int|string|null $key = null,
        $default = null
    ): mixed {
        if ([] === $this->values) {
            return $default;
        }

        $key = null === $key ? $this->getFirstOrDefaultKey() : $key;
        return Arrays::hasKey($key, $this->values)
            ? Arrays::get($key, $this->values)
            : $default;
    }

    public function getAll(): Generator
    {
        yield from $this->values;
    }

    public function getAllScalar(): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => is_scalar($value));
    }

    public function getAllObjects(): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => is_object($value));
    }

    public function getAllTyped(string $type): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => $value instanceof $type);
    }

    /**
     * @param array $array
     * @param callable $eval
     * @return Generator
     */
    private function flattenArrayGenerator(
        array &$array,
        callable $eval
    ): Generator {
        foreach ($array as $value) {
            if (is_array($value)) {
                // Recursively process nested arrays
                yield from $this->flattenArrayGenerator($value, $eval);
            } else {
                // Yield value if of type evaluated by callable
                if ($eval($value)) {
                    yield $value;
                }
            }
        }
    }

    public function getSize(): int
    {
        if ($this->recount) {
            $this->size = Arrays::count($this->values);
            $this->recount = !$this->recount;
        }

        return $this->size;
    }

    public function getType(array|int|string|null $key = null): string
    {
        if ([] === $this->values) {
            throw new LogicException('Empty collection');
        }
        if (is_null($key)) {
            return gettype($this->values[$this->defaultKey]);
        }

        return gettype($this->get($key));
    }

    public function isTypedCollection(): bool
    {
        return $this->collectionType !== null;
    }

    public function add(
        mixed $value,
        array|int|string|null $key = null
    ): self {
        // Validate type if collection is typed
        if ($this->isTypedCollection() && !$value instanceof $this->collectionType) {
            throw new LogicException("Value does not match collection type: {$this->collectionType}");
        }

        Arrays::add(
            null === $key ? $this->getFirstOrDefaultKey() : $key,
            $value,
            $this->values
        );
        $this->recount = true;
        return $this;
    }

    public function set(
        mixed $value,
        array|int|string|null $key = null
    ): self {
        // Validate type if collection is typed
        if ($this->isTypedCollection() && !$value instanceof $this->collectionType) {
            throw new LogicException("Value does not match collection type: {$this->collectionType}");
        }

        Arrays::set(
            null === $key ? $this->getFirstOrDefaultKey() : $key,
            $value,
            $this->values
        );
        $this->recount = true;
        return $this;
    }

    public function delete(array|int|string|null $key = null): mixed
    {
        $this->recount = true;
        return Arrays::delete(
            null === $key ? $this->getFirstOrDefaultKey() : $key,
            $this->values
        );
    }

    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * @return mixed The first key in the collection, if collection empty then $this->defaultKey
     */
    private function getFirstOrDefaultKey(): mixed
    {
        return array_key_first($this->values) ?: $this->defaultKey;
    }

    public function mergeInto(array $values): void
    {
        if ([] === $values) {
            throw new LogicException('Empty collection');
        }

        $this->values = $this->values + $values;
        $this->recount = true;
    }

    public function mergeIntoAt(
        array $values,
        array|int|string|null $key
    ): void {
        if (!$this->has($key)) {
            throw new RuntimeException("$key not found in collection");
        }

        if ('array' === $this->getType($key)) {
            $this->set($this->get($key) + $values, $key);
        } else {
            $this->set([$this->get($key)] + $values, $key);
        }

        $this->recount = true;
    }

    public function getAsCollection($key): Collection
    {
        $newColl = new Collection();
        $tmp = $this->get($key);
        if ($tmp instanceof Collection) {
            foreach ($tmp->getAll() as $k => $v) {
                $newColl->set($v, $k);
            }
        } elseif (is_array($tmp)) {
            foreach ($tmp as $k => $v) {
                $newColl->set($v, $k);
            }
        } else {
            $newColl->set($tmp, $key);
        }

        return $newColl;
    }
}
