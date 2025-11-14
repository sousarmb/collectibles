<?php

declare(strict_types=1);

namespace Collectibles\Tests;

use Collectibles\Collection;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Collectibles\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @covers \Collectibles\Collection::has
     */
    public function testHasReturnsTrueForExistingKey(): void
    {
        $collection = new Collection();
        $collection->set('value', 'test.key');
        $this->assertTrue($collection->has('test.key'));
    }

    /**
     * @covers \Collectibles\Collection::has
     */
    public function testHasReturnsFalseForNonExistingKey(): void
    {
        $collection = new Collection();
        $this->assertFalse($collection->has('test.key'));
    }

    /**
     * @covers \Collectibles\Collection::get
     */
    public function testGetReturnsValueForValidKey(): void
    {
        $collection = new Collection();
        $collection->set('value', 'test.key');
        $this->assertEquals('value', $collection->get('test.key'));
    }

    /**
     * @covers \Collectibles\Collection::get
     */
    public function testGetReturnDefaultValueForInvalidKey(): void
    {
        $collection = new Collection();
        $expected = 'default value';
        $this->assertEquals($expected, $collection->get('test.key', $expected));
    }

    /**
     * @covers \Collectibles\Collection::getAll
     */
    public function testGetAllYieldsAllValues(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $values = iterator_to_array($collection->getAll());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $values);
    }

    /**
     * @covers \Collectibles\Collection::getAllScalar
     */
    public function testGetAllScalarYieldsOnlyScalarValues(): void
    {
        $collection = new Collection();
        $collection->set(42, 'key1');
        $collection->set('string', 'key2');
        $collection->set(new stdClass(), 'key3');
        $collection->add(42, 'key1');
        $collection->add('string', 'key2');
        $collection->add(new stdClass(), 'key3');
        $values = iterator_to_array($collection->getAllScalar(), false);
        $this->assertEquals([42, 42, 'string', 'string'], $values);
    }

    /**
     * @covers \Collectibles\Collection::getAllObjects
     */
    public function testGetAllObjectsYieldsOnlyObjects(): void
    {
        $collection = new Collection();
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $collection->set(42, 'key1');
        $collection->set($obj1, 'key2');
        $collection->set(42, 'key1');
        $collection->add($obj2, 'key2');
        $collection->set($obj3, 'key3');
        $collection->set(42, 'key1');
        $values = iterator_to_array($collection->getAllObjects(), false);
        $this->assertEquals([$obj1, $obj2, $obj3], $values);
    }

    /**
     * @covers \Collectibles\Collection::getAllTyped
     */
    public function testGetAllTypedYieldsTypedObjects(): void
    {
        $collection = new Collection();
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $collection->set(42, 'key1');
        $collection->set($obj1, 'key2');
        $collection->set(42, 'key1');
        $collection->add($obj2, 'key2');
        $collection->set($obj3, 'key3');
        $collection->set(42, 'key1');
        $values = iterator_to_array($collection->getAllTyped(stdClass::class), false);
        $this->assertEquals([$obj1, $obj2, $obj3], $values);
    }

    /**
     * @covers \Collectibles\Collection::getSize
     */
    public function testGetSizeReturnsCorrectCount(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $this->assertEquals(2, $collection->getSize());
    }

    /**
     * @covers \Collectibles\Collection::isTypedCollection
     */
    public function testIsTypedCollectionReturnsTrueForTyped(): void
    {
        $collection = new Collection(stdClass::class);
        $this->assertTrue($collection->isTypedCollection());
    }

    /**
     * @covers \Collectibles\Collection::isTypedCollection
     */
    public function testIsTypedCollectionReturnsFalseForNonTyped(): void
    {
        $collection = new Collection();
        $this->assertFalse($collection->isTypedCollection());
    }

    /**
     * @covers \Collectibles\Collection::add
     */
    public function testAddInsertsValue(): void
    {
        $collection = new Collection();
        $collection->add('value', 'test.key');
        $this->assertEquals('value', $collection->get('test.key'));
    }

    /**
     * @covers \Collectibles\Collection::add
     */
    public function testAddThrowsExceptionForInvalidTypeInTypedCollection(): void
    {
        $this->expectException(LogicException::class);
        $collection = new Collection(stdClass::class);
        $collection->add('string', 'test.key');
    }

    /**
     * @covers \Collectibles\Collection::set
     */
    public function testSetReplacesValue(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'test.key');
        $collection->set('value2', 'test.key');
        $this->assertEquals('value2', $collection->get('test.key'));
    }

    /**
     * @covers \Collectibles\Collection::set
     */
    public function testSetThrowsExceptionForInvalidTypeInTypedCollection(): void
    {
        $this->expectException(LogicException::class);
        $collection = new Collection(stdClass::class);
        $collection->set('string', 'test.key');
    }

    /**
     * @covers \Collectibles\Collection::delete
     */
    public function testDeleteRemovesValueAndReturnsIt(): void
    {
        $collection = new Collection();
        $collection->set('value', 'test.key');
        $collection->set('othervalue', 'test.otherkey');
        $deleted = $collection->delete('test.key');
        $this->assertEquals('value', $deleted);
        $this->assertFalse($collection->has('test.key'));
        $this->assertTrue($collection->has('test.otherkey'));
    }

    /**
     * @covers \Collectibles\Collection::delete
     */
    public function testDeleteThrowsExceptionForInvalidKey(): void
    {
        $this->expectException(RuntimeException::class);
        $collection = new Collection();
        $collection->delete('test.key');
    }

    /**
     * @covers \Collectibles\Collection::toArray
     */
    public function testToArrayReturnsAllValues(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $object = new stdClass();
        $collection->add($object, 'key2');
        $this->assertEquals(['key1' => 'value1', 'key2' => ['value2', $object]], $collection->toArray());
    }

    /**
     * @covers \Collectibles\Collection::mergeInto
     */
    public function testMergeArrayIntoCollection(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $collection->mergeInto(['key3' => 'value3']);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'], $collection->toArray());
    }

    /**
     * @covers \Collectibles\Collection::mergeInto
     */
    public function testGetValueFromMergedArrayIntoCollection(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $collection->mergeInto(['key3' => 'value3']);
        $this->assertEquals('value3', $collection->get('key3'));
    }

    /**
     * @covers \Collectibles\Collection::mergeIntoAt
     */
    public function testMergeArrayIntoCollectionAtSpecificKey(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $collection->mergeIntoAt(['key3' => 'value3'], 'key2');
        $this->assertEquals(['key1' => 'value1', 'key2' => ['value2', 'key3' => 'value3']], $collection->toArray());
    }

    /**
     * @covers \Collectibles\Collection::mergeIntoAt
     */
    public function testMergeArrayIntoCollectionAtSpecificKeyGetSpecificKey(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $collection->mergeIntoAt(['key3' => 'value3'], 'key2');
        $this->assertEquals('value3', $collection->get('key2.key3'));
    }

    /**
     * @covers \Collectibles\Collection::mergeIntoAt
     */
    public function testThrowRuntimeExceptionOnMergeArrayIntoCollectionAtSpecificInvalidKey(): void
    {
        $collection = new Collection();
        $collection->set('value1', 'key1');
        $collection->set('value2', 'key2');
        $this->expectException(RuntimeException::class);
        $collection->mergeIntoAt(['key3' => 'value3'], 'key22');
    }

    /**
     * @covers \Collectibles\Collection::getAsCollection
     */
    public function testReturnsCollectionWithSingleValueWhenScalar(): void
    {
        $collection = new Collection();
        $collection->set('hello', 'greeting');

        $result = $collection->getAsCollection('greeting');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame('hello', $result->get('greeting'));
        $this->assertCount(1, iterator_to_array($result->getAll()));
    }

    /**
     * @covers \Collectibles\Collection::getAsCollection
     */
    public function testReturnsCollectionFromArrayValue(): void
    {
        $collection = new Collection();
        $collection->set(['a' => 1, 'b' => 2], 'data');

        $result = $collection->getAsCollection('data');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame(1, $result->get('a'));
        $this->assertSame(2, $result->get('b'));
        $this->assertCount(2, iterator_to_array($result->getAll()));
    }

    /**
     * @covers \Collectibles\Collection::getAsCollection
     */
    public function testReturnsCollectionFromNestedCollection(): void
    {
        $inner = new Collection();
        $inner->add('x', 'foo');
        $inner->add('y', 'bar');

        $collection = new Collection();
        $collection->set($inner, 'nested');

        $result = $collection->getAsCollection('nested');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame('x', $result->get('foo'));
        $this->assertSame('y', $result->get('bar'));
        $this->assertCount(2, iterator_to_array($result->getAll()));
    }

    /**
     * @covers \Collectibles\Collection::getAsCollection
     */
    public function testReturnsNewCollectionWithKeyWhenValueIsNull(): void
    {
        $collection = new Collection();
        $collection->set(null, 'empty');

        $result = $collection->getAsCollection('empty');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertNull($result->get('empty'));
    }

    /**
     * @covers \Collectibles\Collection::getAsCollection
     */
    public function testHandlesEmptyArrayGracefully(): void
    {
        $collection = new Collection();
        $collection->set([], 'empty');

        $result = $collection->getAsCollection('empty');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, iterator_to_array($result->getAll()));
    }
}
