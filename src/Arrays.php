<?php

declare(strict_types=1);

namespace Collectibles;

use Closure;
use LogicException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use RuntimeException;

abstract class Arrays
{
    private const DEFAULT_SEPARATOR = '.';

    /**
     * Flatten a multi-dimensional array
     *
     * @param array $array
     * @return array
     */
    public static function flatten(array $array): array
    {
        return iterator_to_array(
            new RecursiveIteratorIterator(
                new RecursiveArrayIterator($array, RecursiveArrayIterator::CHILD_ARRAYS_ONLY)
            ),
            false
        );
    }

    /**
     * Check if key exists in nested array using dot notation or array of keys
     *
     * @param array|string $needle
     * @param array $haystack
     * @param string $separator
     * @return bool
     */
    public static function hasKey(
        array|string $needle,
        array $haystack,
        string $separator = self::DEFAULT_SEPARATOR
    ): bool {
        if ([] === $haystack) {
            return false;
        }

        [$key, $needle] = static::getKeyFromNeedle($needle, $separator);
        if (!isset($haystack[$key])) {
            return false;
        }

        return $needle
            ? static::hasKey($needle, $haystack[$key])
            : true;
    }

    /**
     * Get value from nested array using dot notation or array of keys
     *
     * @param array|string $needle
     * @param array $haystack
     * @param string $separator
     * @return mixed
     * @throws RuntimeException When haystack is empty
     * @throws RuntimeException When key is invalid 
     */
    public static function get(
        array|string $needle,
        array &$haystack,
        string $separator = self::DEFAULT_SEPARATOR
    ): mixed {
        if ([] === $haystack) {
            throw new RuntimeException('Empty haystack');
        }

        [$key, $needle] = static::getKeyFromNeedle($needle, $separator);
        if (!isset($haystack[$key])) {
            throw new RuntimeException("Invalid key: $key");
        }

        return $needle
            ? static::get($needle, $haystack[$key])
            : $haystack[$key];
    }

    /**
     * Add a value to a nested array using dot notation or array of keys, duplicates allowed
     *
     * @param array|string $needle
     * @param mixed $value
     * @param array $haystack
     * @param string $separator
     * @return void
     */
    public static function add(
        array|string $needle,
        mixed $value,
        array &$haystack,
        string $separator = self::DEFAULT_SEPARATOR
    ): void {
        [$key, $needle] = static::getKeyFromNeedle($needle, $separator);
        if ([] === $needle) {
            if (!isset($haystack[$key])) {
                $haystack[$key] = $value;
            } elseif (is_array($haystack[$key])) {
                // Store duplicates
                $haystack[$key][] = $value;
            } else {
                // Turn into array, store duplicate
                $haystack[$key] = [$haystack[$key], $value];
            }

            return;
        }
        if (!isset($haystack[$key])) {
            $haystack[$key] = [];
        }

        static::add($needle, $value, $haystack[$key], $separator);
    }

    /**
     * Set or replace a value in a nested array using dot notation or array of keys
     *
     * @param array|string $needle
     * @param mixed $value
     * @param array $haystack
     * @param string $separator
     * @return void
     */
    public static function set(
        array|string $needle,
        mixed $value,
        array &$haystack,
        string $separator = self::DEFAULT_SEPARATOR
    ): void {
        [$key, $needle] = static::getKeyFromNeedle($needle, $separator);
        if ([] === $needle) {
            $haystack[$key] = $value;
            return;
        }
        if (!isset($haystack[$key])) {
            $haystack[$key] = [];
        }

        static::set($needle, $value, $haystack[$key], $separator);
    }

    /**
     * Sort (in place) an array of array by key value and direction
     * 
     * @param array $arrayToSort
     * @param array $keyDirection [['key', 'asc|desc'], ...]
     * @param bool $keepKeys
     * @return void
     * @throws RuntimeException When array to sort is empty
     * @throws RuntimeException When invalid key-direction pair found
     */
    public static function sortArraysArrayByKeyAndDirection(
        array &$arrayToSort,
        array $keyDirection,
        bool $keepKeys = false
    ): void {
        if ([] === $arrayToSort) {
            throw new RuntimeException('Empty array to sort');
        }

        foreach ($keyDirection as $pair) {
            if (!is_array($pair) || count($pair) !== 2 || !is_string($pair[0]) || !in_array(strtolower($pair[1]), ['asc', 'desc'])) {
                throw new RuntimeException('Invalid key-direction pair');
            }
        }
        $keepKeys
            ? uasort($arrayToSort, static::arraySortingFunction($keyDirection))
            : usort($arrayToSort, static::arraySortingFunction($keyDirection));
    }

    /**
     * 
     * @param array $parameters
     * @return Closure
     */
    private static function arraySortingFunction(array $parameters): Closure
    {
        return function (array $a, array $b) use ($parameters) {
            return array_reduce(
                $parameters,
                function ($result, $key) use ($a, $b) {
                    if ($result) {
                        return $result;
                    }
                    list($column, $order) = $key;
                    if (is_int($a[$column]) || is_float($a[$column])) {
                        return $order == 'asc' || $order == 'ASC' ? $a[$column] <=> $b[$column] : $b[$column] <=> $a[$column];
                    }
                    // string
                    return $order == 'asc' || $order == 'ASC' ? call_user_func('strcmp', $a[$column], $b[$column]) : call_user_func('strcmp', $b[$column], $a[$column]);
                }
            );
        };
    }

    /**
     * Sort (in place) an array of objects by multiple properties and directions
     *
     * @param array $arrayToSort
     * @param array $propertyDirection [['property', 'asc|desc'], ...]
     * @param bool $keepKeys
     * @return void
     * @throws RuntimeException When object array to sort is empty 
     * @throws RuntimeException When array element is not an object
     * @throws LogicException When invalid key-direction pair found
     * @throws RuntimeException When object property not found
     */
    public static function sortObjectsArrayByPropertyAndDirection(
        array &$arrayToSort,
        array $propertyDirection,
        bool $keepKeys = false
    ): void {
        if ([] === $arrayToSort) {
            throw new RuntimeException('Empty object array to sort');
        }
        $firstElement = reset($arrayToSort);
        if (!is_object($firstElement)) {
            throw new RuntimeException('Array elements must be objects');
        }

        foreach ($propertyDirection as $pair) {
            if (!is_array($pair) || count($pair) !== 2 || !is_string($pair[0]) || !in_array(strtolower($pair[1]), ['asc', 'desc'])) {
                throw new LogicException("Invalid property-direction pair");
            }
            if (!property_exists($firstElement, $pair[0])) {
                throw new RuntimeException("Property not found: {$pair[0]}");
            }
        }
        $keepKeys
            ? uasort($arrayToSort, static::objectSortingfunction($propertyDirection))
            : usort($arrayToSort, static::objectSortingfunction($propertyDirection));
    }

    /**
     * 
     * @param array $parameters
     * @return Closure
     */
    private static function objectSortingfunction(array $parameters): Closure
    {
        return function ($a, $b) use ($parameters) {
            return array_reduce(
                $parameters,
                function ($result, $pair) use ($a, $b) {
                    if ($result !== 0) {
                        return $result;
                    }
                    list($property, $direction) = $pair;
                    $valueA = $a->$property;
                    $valueB = $b->$property;
                    if (is_int($valueA) || is_float($valueA)) {
                        return ($direction === 'asc' || $direction === 'ASC') ? $valueA <=> $valueB : $valueB <=> $valueA;
                    }
                    // string
                    return ($direction === 'asc' || $direction === 'ASC') ? strcmp($valueA, $valueB) : strcmp($valueB, $valueA);
                },
                0
            );
        };
    }

    /**
     * Count all values with empty arrays included
     * 
     * @param array $array
     * @return int
     */
    public static function count(array &$array): int
    {
        $count = 0;
        if ([] === $array) {
            return $count;
        }

        foreach ($array as $value) {
            $count += is_array($value) ? static::count($value) : 1;
        }
        return $count;
    }

    /**
     * Delete a value from nested array using dot notation or array of keys
     * 
     * @param array|string $needle
     * @param array $haystack
     * @param array|string $separator
     * @return mixed The deleted value
     * @throws RuntimeException When haystack is empty
     * @throws RuntimeException When key is invalid
     */
    public static function delete(
        array|string $needle,
        array &$haystack,
        string $separator = self::DEFAULT_SEPARATOR
    ): mixed {
        if ([] === $haystack) {
            throw new RuntimeException('Empty haystack');
        }

        [$key, $needle] = static::getKeyFromNeedle($needle, $separator);
        if (!isset($haystack[$key])) {
            throw new RuntimeException("Invalid key: $key");
        }
        if ($needle) {
            return static::delete($needle, $haystack[$key]);
        }

        $temp = $haystack[$key];
        unset($haystack[$key]);
        return $temp;
    }

    /**
     * 
     * @param array|string $needle
     * @param string $separator
     * @return array<int, mixed> First value is $key, second is remaining needle
     * @throws RuntimeException When needle is empty
     */
    private static function getKeyFromNeedle(array|string $needle, string $separator): array
    {
        if ([] === $needle) {
            throw new RuntimeException('Empty needle');
        } elseif (is_string($needle)) {
            if ('' === trim($needle)) {
                throw new RuntimeException('Empty needle');
            }

            $needle = explode($separator, $needle);
        }

        return [array_shift($needle), $needle];
    }

    /**
     * Get all the parent nodes key
     * 
     * @param array $array The same structure as the original array
     */
    public static function getParentNodesKey(array &$array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = static::getParentNodesKey($value);
            }
        }
        return $result;
    }
}
