<?php

declare(strict_types=1);

namespace Collectibles\Tests;

use Collectibles\Arrays;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Collectibles\Arrays
 */
class ArraysTest extends TestCase
{
    /**
     * @covers \Collectibles\Arrays::flatten
     */
    public function testFlattenWithNestedArray(): void
    {
        $inputArray = ['a' => 1, 'b' => ['c' => 2, 'd' => [3, 4, ['e' => 5]]]];
        $expected = [1, 2, 3, 4, 5];
        $this->assertEquals($expected, Arrays::flatten($inputArray));
    }

    /**
     * @covers \Collectibles\Arrays::flatten
     */
    public function testFlattenWithEmptyArray(): void
    {
        $inputArray = [];
        $this->assertEquals([], Arrays::flatten($inputArray));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]], 13];
        $this->assertTrue(Arrays::hasKey(0, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithInvalidIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]], 13];
        $this->assertFalse(Arrays::hasKey(1, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithDotNotation(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]]];
        $this->assertTrue(Arrays::hasKey('a.b.c', $haystack));
        $this->assertTrue(Arrays::hasKey('a.b.d', $haystack));
        $this->assertFalse(Arrays::hasKey('a.b.e', $haystack));
        $this->assertFalse(Arrays::hasKey('x.y.z', $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithNumericNeedle(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]], 13];
        $this->assertTrue(Arrays::hasKey('0', $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithArray(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]]];
        $needle = ['a', 'b', 'c'];
        $this->assertTrue(Arrays::hasKey($needle, $haystack));
        $needle = ['a', 'b', 'e'];
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithArrayUsingInvalidNeedle(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]]];
        $needle = ['a', 'e', 'c'];
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
        $needle = ['a', 'b', 'f'];
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
        $needle = ['g.d.f'];
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
        $needle = '12';
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithCustomSeparator(): void
    {
        $haystack = ['a' => ['b' => ['c' => 1]]];
        $this->assertTrue(Arrays::hasKey('a-b-c', $haystack, '-'));
        $this->assertFalse(Arrays::hasKey('a-b-d', $haystack, '-'));
    }

    /**
     * @covers \Collectibles\Arrays::hasKey
     */
    public function testHasKeyWithEmptyHaystack(): void
    {
        $haystack = [];
        $this->assertFalse(Arrays::hasKey('a.b.c', $haystack));
        $needle = ['a', 'b', 'c'];
        $this->assertFalse(Arrays::hasKey($needle, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::get
     */
    public function testGetWithDotNotation(): void
    {
        $haystack = ['a' => ['b' => ['c' => 42, 'd' => 'test']]];
        $this->assertEquals(42, Arrays::get('a.b.c', $haystack));
        $this->assertEquals('test', Arrays::get('a.b.d', $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::get
     */
    public function testGetWithArray(): void
    {
        $haystack = ['a' => ['b' => ['c' => 42]]];
        $needle = ['a', 'b', 'c'];
        $this->assertEquals(42, Arrays::get($needle, $haystack));
    }

    /**
     * @covers \Collectibles\Arrays::get
     */
    public function testGetWithCustomSeparator(): void
    {
        $haystack = ['a' => ['b' => ['c' => 42]]];
        $this->assertEquals(42, Arrays::get('a-b-c', $haystack, '-'));
    }

    /**
     * @covers \Collectibles\Arrays::get
     */
    public function testGetThrowsOnEmptyHaystack(): void
    {
        $haystack = [];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty haystack');
        Arrays::get('a.b.c', $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::get
     */
    public function testGetThrowsOnInvalidKey(): void
    {
        $haystack = ['a' => ['b' => ['c' => 42]]];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid key: x');
        Arrays::get('x.y.z', $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddWithIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => [1]], 1];
        Arrays::add(0, 2, $haystack);
        Arrays::add(0, 3, $haystack);
        $this->assertEquals(['a' => ['b' => [1]], [1, 2, 3]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddWithDotNotation(): void
    {
        $haystack = ['a' => ['b' => [1]]];
        Arrays::add('a.b', 2, $haystack);
        $this->assertEquals(['a' => ['b' => [1, 2]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddWithArray(): void
    {
        $haystack = ['a' => ['b' => [1]]];
        $needle = ['a', 'b'];
        Arrays::add($needle, 2, $haystack);
        $this->assertEquals(['a' => ['b' => [1, 2]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddWithCustomSeparator(): void
    {
        $haystack = ['a' => ['b' => [1]]];
        Arrays::add('a-b', 2, $haystack, '-');
        $this->assertEquals(['a' => ['b' => [1, 2]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddToNonArrayValue(): void
    {
        $haystack = ['a' => ['b' => 1]];
        Arrays::add('a.b', 2, $haystack);
        $this->assertEquals(['a' => ['b' => [1, 2]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::add
     */
    public function testAddToNonExistentPath(): void
    {
        $haystack = [];
        Arrays::add('a.b.c', 42, $haystack);
        $this->assertEquals(['a' => ['b' => ['c' => 42]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::set
     */
    public function testSetWithIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => 1], ['c', 'd']];
        Arrays::set(0, 'd', $haystack);
        $this->assertEquals(['a' => ['b' => 1], 'd'], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::set
     */
    public function testSetWithDotNotation(): void
    {
        $haystack = ['a' => ['b' => 1]];
        Arrays::set('a.b', 2, $haystack);
        $this->assertEquals(['a' => ['b' => 2]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::set
     */
    public function testSetWithArray(): void
    {
        $haystack = ['a' => ['b' => 1]];
        $needle = ['a', 'b'];
        Arrays::set($needle, 2, $haystack);
        $this->assertEquals(['a' => ['b' => 2]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::set
     */
    public function testSetWithCustomSeparator(): void
    {
        $haystack = ['a' => ['b' => 1]];
        Arrays::set('a-b', 2, $haystack, '-');
        $this->assertEquals(['a' => ['b' => 2]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::set
     */
    public function testSetToNonExistentPath(): void
    {
        $haystack = [];
        Arrays::set('a.b.c', 42, $haystack);
        $this->assertEquals(['a' => ['b' => ['c' => 42]]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAndDirectionAsc(): void
    {
        $arrayToSort = [
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35]
        ];
        $keyDirection = [['name', 'asc']];
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
        $this->assertEquals([
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Charlie', 'age' => 35]
        ], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyNameAndDirectionAscAndKeyAgeAndDirectionDesc(): void
    {
        $arrayToSort = [
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
            ['name' => 'Alice', 'age' => 26],
        ];
        $keyDirection = [['name', 'asc'], ['age', 'desc']];
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
        $this->assertEquals([
            ['name' => 'Alice', 'age' => 26],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Charlie', 'age' => 35]
        ], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAgeAndDirectionAscAndKeyNameAndDirectionAsc(): void
    {
        $arrayToSort = [
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
            ['name' => 'Alice', 'age' => 26],
            ['name' => 'Zed', 'age' => 25],
        ];
        $keyDirection = [['age', 'asc'], ['name', 'asc']];
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
        $this->assertEquals([
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Zed', 'age' => 25],
            ['name' => 'Alice', 'age' => 26],
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Charlie', 'age' => 35]
        ], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAgeAndDirectionAscAndKeyNameAndDirectionDesc(): void
    {
        $arrayToSort = [
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
            ['name' => 'Alice', 'age' => 26],
            ['name' => 'Zed', 'age' => 25],
        ];
        $keyDirection = [['age', 'asc'], ['name', 'desc']];
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
        $this->assertEquals([
            ['name' => 'Zed', 'age' => 25],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Alice', 'age' => 26],
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Charlie', 'age' => 35]
        ], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAndDirectionDescWithKeepKeys(): void
    {
        $arrayToSort = [
            10 => ['name' => 'Bob', 'age' => 30],
            20 => ['name' => 'Alice', 'age' => 25]
        ];
        $keyDirection = [['age', 'desc']];
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection, true);
        $this->assertEquals([
            10 => ['name' => 'Bob', 'age' => 30],
            20 => ['name' => 'Alice', 'age' => 25]
        ], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAndDirectionThrowsOnEmptyArray(): void
    {
        $arrayToSort = [];
        $keyDirection = [['name', 'asc']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty array to sort');
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
    }

    /**
     * @covers \Collectibles\Arrays::sortArraysArrayByKeyAndDirection
     */
    public function testSortArraysArrayByKeyAndDirectionThrowsOnInvalidPair(): void
    {
        $arrayToSort = [['name' => 'Bob', 'age' => 30]];
        $keyDirection = [['name', 'invalid']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid key-direction pair');
        Arrays::sortArraysArrayByKeyAndDirection($arrayToSort, $keyDirection);
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAndDirectionAsc(): void
    {
        $obj1 = new stdClass();
        $obj1->name = 'Bob';
        $obj1->age = 30;
        $obj2 = new stdClass();
        $obj2->name = 'Alice';
        $obj2->age = 25;
        $obj3 = new stdClass();
        $obj3->name = 'Charlie';
        $obj3->age = 35;
        $obj4 = new stdClass();
        $obj4->name = 'Zed';
        $obj4->age = 35;
        $obj5 = new stdClass();
        $obj5->name = 'Alice';
        $obj5->age = 25;
        $arrayToSort = [$obj3, $obj1, $obj2, $obj4, $obj5];
        $propertyDirection = [['name', 'asc']];
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection);
        $this->assertEquals(
            ['Alice', 'Alice', 'Bob', 'Charlie', 'Zed'],
            [$arrayToSort[0]->name, $arrayToSort[1]->name, $arrayToSort[2]->name, $arrayToSort[3]->name, $arrayToSort[4]->name]
        );
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAndDirectionDescWithKeepKeys(): void
    {
        $obj1 = new stdClass();
        $obj1->name = 'Bob';
        $obj1->age = 30;
        $obj2 = new stdClass();
        $obj2->name = 'Alice';
        $obj2->age = 25;
        $obj3 = new stdClass();
        $obj3->name = 'Charlie';
        $obj3->age = 35;
        $obj4 = new stdClass();
        $obj4->name = 'Zed';
        $obj4->age = 35;
        $obj5 = new stdClass();
        $obj5->name = 'Alice';
        $obj5->age = 25;
        $arrayToSort = [10 => $obj1, 20 => $obj2, 30 => $obj3, 40 => $obj4, 50 => $obj5];
        $propertyDirection = [['age', 'desc']];
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection, true);
        $this->assertEquals([30 => $obj3, 40 => $obj4, 10 => $obj1, 20 => $obj2, 50 => $obj5], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAgeAndDirectionDescAndPropertyNameAndDirectionDescWithKeepKeys(): void
    {
        $obj1 = new stdClass();
        $obj1->name = 'Bob';
        $obj1->age = 30;
        $obj2 = new stdClass();
        $obj2->name = 'Alice';
        $obj2->age = 25;
        $obj3 = new stdClass();
        $obj3->name = 'Charlie';
        $obj3->age = 35;
        $obj4 = new stdClass();
        $obj4->name = 'Zed';
        $obj4->age = 35;
        $obj5 = new stdClass();
        $obj5->name = 'Alice';
        $obj5->age = 25;
        $arrayToSort = [10 => $obj1, 20 => $obj2, 30 => $obj3, 40 => $obj4, 50 => $obj5];
        $propertyDirection = [['age', 'desc'], ['name', 'desc']];
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection, true);
        $this->assertEquals([40 => $obj4, 30 => $obj3, 10 => $obj1, 20 => $obj2, 50 => $obj5], $arrayToSort);
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAndDirectionThrowsOnEmptyArray(): void
    {
        $arrayToSort = [];
        $propertyDirection = [['name', 'asc']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty object array to sort');
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection);
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAndDirectionThrowsOnNonObject(): void
    {
        $arrayToSort = [['name' => 'Bob']];
        $propertyDirection = [['name', 'asc']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array elements must be objects');
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection);
    }

    /**
     * @covers \Collectibles\Arrays::sortObjectsArrayByPropertyAndDirection
     */
    public function testSortObjectsArrayByPropertyAndDirectionThrowsOnInvalidProperty(): void
    {
        $obj = new stdClass();
        $obj->name = 'Bob';
        $arrayToSort = [$obj];
        $propertyDirection = [['age', 'asc']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Property not found: age');
        Arrays::sortObjectsArrayByPropertyAndDirection($arrayToSort, $propertyDirection);
    }

    /**
     * @covers \Collectibles\Arrays::count
     */
    public function testCountWithNestedArray(): void
    {
        $array = ['a' => 1, 'b' => ['c' => 2, 'd' => [3, 4, ['e' => 5]]]];
        $this->assertEquals(5, Arrays::count($array));
    }

    /**
     * @covers \Collectibles\Arrays::count
     */
    public function testCountWithEmptyArray(): void
    {
        $array = [];
        $this->assertEquals(0, Arrays::count($array));
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteWithIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => 42, 'c' => 43], 44];
        $this->assertEquals(44, Arrays::delete(0, $haystack));
        $this->assertEquals(['a' => ['b' => 42, 'c' => 43]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteWithInvalidIntegerNeedle(): void
    {
        $haystack = ['a' => ['b' => 42, 'c' => 43], 44];
        $this->expectException(RuntimeException::class);
        Arrays::delete(1, $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteWithDotNotation(): void
    {
        $haystack = ['a' => ['b' => 42, 'c' => 43]];
        $this->assertEquals(42, Arrays::delete('a.b', $haystack));
        $this->assertEquals(['a' => ['c' => 43]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteWithArray(): void
    {
        $haystack = ['a' => ['b' => 42, 'c' => 43]];
        $needle = ['a', 'b'];
        $this->assertEquals(42, Arrays::delete($needle, $haystack));
        $this->assertEquals(['a' => ['c' => 43]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteWithCustomSeparator(): void
    {
        $haystack = ['a' => ['b' => 42, 'c' => 43]];
        $this->assertEquals(42, Arrays::delete('a-b', $haystack, '-'));
        $this->assertEquals(['a' => ['c' => 43]], $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteThrowsOnEmptyHaystack(): void
    {
        $haystack = [];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty haystack');
        Arrays::delete('a.b', $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::delete
     */
    public function testDeleteThrowsOnInvalidKey(): void
    {
        $haystack = ['a' => ['b' => 42]];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid key: x');
        Arrays::delete('x.y', $haystack);
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyWithEmptyArray(): void
    {
        $array = [];
        $result = Arrays::getParentNodesKey($array);
        $this->assertEquals([], $result, 'Empty array should return empty array');
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyWithFlatArray(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::getParentNodesKey($array);
        $this->assertEquals([], $result, 'Flat array with no nested arrays should return empty array');
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyWithSingleLevelNesting(): void
    {
        $array = [
            'a' => ['x' => 1, 'y' => 2],
            'b' => 3,
            'c' => ['z' => 4]
        ];
        $expected = [
            'a' => [],
            'c' => []
        ];
        $result = Arrays::getParentNodesKey($array);
        $this->assertEquals($expected, $result, 'Should return keys of nested arrays only');
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyWithMultipleLevelNesting(): void
    {
        $array = [
            'a' => [
                'x' => 1,
                'y' => ['p' => 2, 'q' => 3],
                'z' => ['r' => 4]
            ],
            'b' => 5,
            'c' => [
                'm' => ['n' => 6]
            ]
        ];
        $expected = [
            'a' => [
                'y' => [],
                'z' => []
            ],
            'c' => [
                'm' => []
            ]
        ];
        $result = Arrays::getParentNodesKey($array);
        $this->assertEquals($expected, $result, 'Should return nested structure of parent keys');
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyWithDeepNesting(): void
    {
        $array = [
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 1
                    ]
                ]
            ]
        ];
        $expected = [
            'a' => [
                'b' => [
                    'c' => []
                ]
            ]
        ];
        $result = Arrays::getParentNodesKey($array);
        $this->assertEquals($expected, $result, 'Should handle deep nesting correctly');
    }

    /**
     * @covers \Collectibles\Arrays::getParentNodesKey
     */
    public function testGetParentNodesKeyDoesNotModifyOriginalArray(): void
    {
        $array = [
            'a' => [
                'x' => 1,
                'y' => ['p' => 2]
            ]
        ];
        $original = $array;
        Arrays::getParentNodesKey($array);
        $this->assertEquals($original, $array, 'Original array should remain unchanged');
    }
}
