<?php

declare(strict_types=1);

namespace Collectibles\Contracts;

use Collectibles\Contracts\IO as IOContract;
use Generator;
use LogicException;

interface Collection extends IOContract
{
    /**
     * Check if collection has key
     * 
     * @param array|int|string $key
     * @return bool
     */
    public function has(array|int|string $key): bool;

    /**
     * Get collection element by key  
     *
     * @param array|int|string|null $key If null the first element in the collection is used
     * @param mixed $default Returned if key not found
     * @return mixed
     */
    public function get(
        array|int|string|null $key = null,
        $default = null
    ): mixed;

    /**
     * Get all collection elements
     * 
     * @return Generator<int|string, mixed> With collection keys
     */
    public function getAll(): Generator;

    /**
     * Get all scalar collection elements
     * 
     * @return Generator<int|string, scalar> Without collection keys
     */
    public function getAllScalar(): Generator;

    /**
     * Get all collection elements of type object
     * 
     * @return Generator<int, object> Does not retain collection keys
     */
    public function getAllObjects(): Generator;

    /**
     * Get all collection elements of a specified type
     *
     * @return Generator<int, object> Does not retain collection keys
     */
    public function getAllTyped(string $type): Generator;

    /**
     * Get collection size, counts all elements not just keys
     * 
     * @return int
     */
    public function getSize(): int;

    /**
     * Get value type in key
     * 
     * @param array|int|string|null $key If null the first element in the collection is used
     * @throws LogicException If collection empty
     * @return string
     */
    public function getType(array|int|string|null $key = null): string;

    /**
     * Check if collection is just a specified type
     * 
     * @return bool
     */
    public function isTypedCollection(): bool;

    /**
     * Add element to collection, duplicates allowed
     * 
     * @param mixed $value
     * @param array|int|string|null $key
     * @return self
     * @throws LogicException If value type does match collection type (if typed collection)
     */
    public function add(
        mixed $value,
        array|int|string|null $key = null
    ): self;

    /**
     * Set or replace element at specific collection key
     * 
     * @param mixed $value
     * @param array|int|string|null $key
     * @return self
     * @throws LogicException If value type does match collection type (if typed collection)
     */
    public function set(
        mixed $value,
        array|int|string|null $key = null
    ): self;

    /**
     * Delete specific collection key
     * 
     * @param mixed $value
     * @param array|int|string|null $key
     * @return mixed
     */
    public function delete(array|int|string|null $key = null): mixed;

    /**
     * Return collections as array
     * 
     * @return array<mixed, mixed>
     */
    public function toArray(): array;

    /**
     * Add to collection
     * 
     * @param array $values The array to merge into the collection
     * @throws LogicException If collection empty
     */
    public function mergeInto(array $values): void;

    /**
     * Add to collection at specified key. 
     * If key is not an array, it is made into one.
     * 
     * @param array $values
     * @param array|int|string|null $key 
     * @throws RuntimeException If $key not found
     */
    public function mergeIntoAt(
        array $values,
        array|int|string|null $key
    ): void;

    /**
     * Get key => value from this collection as a new collection
     * 
     * @param string $key The key => value to get
     * @return Collection
     */
    public function getAsCollection($key): Collection;
}
