<?php

declare(strict_types=1);

namespace Collectibles\Tests;

use Collectibles\IO;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class IOTest extends TestCase
{
    /**
     * @covers \Collectibles\IO::get
     */
    public function testGetThrowsExceptionForNonExistentProperty(): void
    {
        $io = new IO();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Trying to get undefined IO property: nonExistent');
        $io->get('nonExistent');
    }

    /**
     * @covers \Collectibles\IO::get
     */
    public function testGetReturnsPropertyValue(): void
    {
        // Create a class that extends IO to add a testable property
        $testClass = new readonly class extends IO {
            public readonly string $testProperty;

            public function __construct()
            {
                $this->testProperty = 'testValue';
            }
        };

        $this->assertSame('testValue', $testClass->get('testProperty'));
    }
}
