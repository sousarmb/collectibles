<?php

declare(strict_types=1);

namespace Collectibles;

use Collectibles\Arrays;
use Collectibles\Contracts\IO;
use Generator;
use LogicException;
use RuntimeException;

class Collection implements IO
{
    private bool $recount = false;
    private int $size = 0;
    private array $values = [];

    /**
     * 
     * @param string|null $collectionType Use valid class names only, triggers class autoloader
     * @throws LogicException If class name is not valid
     */
    public function __construct(
        private readonly ?string $collectionType = null
    ) {
        $valid = null === $collectionType ?: class_exists($collectionType, true);
        if (!$valid) {
            throw new LogicException('Use valid class names only');
        }
    }

    /**
     * Check if collection has key
     * 
     * @param array|string $key
     * @return bool
     */
    public function has(array|string $key): bool
    {
        return Arrays::hasKey($key, $this->values);
    }

    /**
     * Get collection element by key  
     *
     * @param array|string|null $key If null the first element in the collection is used
     * @param mixed $default Returned if key not found
     * @return mixed
     */
    public function get(
        array|string|null $key,
        $default = null
    ): mixed {
        if ([] === $this->values) {
            return $default;
        }

        $key = null === $key ? $this->getFirstKey() : $key;
        if (!Arrays::hasKey($key, $this->values)) {
            return $default;
        }

        return Arrays::get($key, $this->values);
    }

    /**
     * Get all collection elements
     * 
     * @return Generator<int|string, mixed> With collection keys
     */
    public function getAll(): Generator
    {
        yield from $this->values;
    }

    /**
     * Get all scalar collection elements
     * 
     * @return Generator<int|string, scalar> Without collection keys
     */
    public function getAllScalar(): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => is_scalar($value));
    }

    /**
     * Get all collection elements of type object
     * 
     * @return Generator<int, object> Does not retain collection keys
     */
    public function getAllObjects(): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => is_object($value));
    }

    /**
     * Get all collection elements of a specified type
     *
     * @return Generator<int, object> Does not retain collection keys
     */
    public function getAllTyped(string $type): Generator
    {
        yield from $this->flattenArrayGenerator($this->values, fn($value) => $value instanceof $type);
    }

    /**
     * 
     * @param array $array
     * @param callable $eval
     * @return Generator
     */
    private function flattenArrayGenerator(array &$array, callable $eval): Generator
    {
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

    /**
     * Get collection size, counts all elements not just keys
     * 
     * @return int
     */
    public function getSize(): int
    {
        if ($this->recount) {
            $this->size = Arrays::count($this->values);
            $this->recount = !$this->recount;
        }

        return $this->size;
    }

    /**
     * Check if collection is just a specified type
     * 
     * @return bool
     */
    public function isTypedCollection(): bool
    {
        return $this->collectionType !== null;
    }

    /**
     * 
     * Add element to collection, duplicates allowed
     * 
     * @param mixed $value
     * @param string|null $key
     * @return self
     * @throws LogicException If value type does match collection type (if typed collection)
     */
    public function add(mixed $value, array|string|null $key = null): self
    {
        // Validate type if collection is typed
        if ($this->isTypedCollection() && !$value instanceof $this->collectionType) {
            throw new LogicException("Value does not match collection type: {$this->collectionType}");
        }

        Arrays::add(
            null === $key ? $this->getFirstKey() : $key,
            $value,
            $this->values
        );
        $this->recount = true;
        return $this;
    }

    /**
     * Set or replace element at specific collection key
     * 
     * @param mixed $value
     * @param array|string|null $key
     * @return self
     * @throws LogicException If value type does match collection type (if typed collection)
     */
    public function set(mixed $value, array|string|null $key = null): self
    {
        // Validate type if collection is typed
        if ($this->isTypedCollection() && !$value instanceof $this->collectionType) {
            throw new LogicException("Value does not match collection type: {$this->collectionType}");
        }

        Arrays::set(
            null === $key ? $this->getFirstKey() : $key,
            $value,
            $this->values
        );
        $this->recount = true;
        return $this;
    }

    /**
     * Delete specific collection key
     * 
     * @param mixed $value
     * @param array|string|null $key
     * @return mixed
     */
    public function delete(array|string|null $key = null): mixed
    {
        $this->recount = true;
        return Arrays::delete(
            null === $key ? $this->getFirstKey() : $key,
            $this->values
        );
    }

    /**
     * 
     * @return array<mixed, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * 
     * @return int|string The first key in the collection
     */
    private function getFirstKey(): int|string
    {
        if ([] === $this->values) {
            return 0;
        }

        reset($this->values);
        return (string)key($this->values);
    }

    /**
     * 
     * @param array $values The array to merge into the collection
     */
    public function mergeInto(array $values): void
    {
        if ([] === $values) return;

        $this->values = $this->values + $values;
    }

    /**
     * 
     * @param array $values
     * @param array|string|null $key 
     * @throws RuntimeException If $key not found
     */
    public function mergeIntoAt(
        array $values,
        array|string|null $key
    ): void {
        if (!$this->has($key)) {
            throw new RuntimeException("$key not found in collection");
        }

        $this->set([$this->get($key)] + $values, $key);
    }
}
