<?php

/*
 * The MIT License
 *
 * Copyright 2024 rsousa.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

declare(strict_types=1);

namespace Collectibles;

use Collectibles\Contracts\IO;
use Generator;
use LogicException;
use RuntimeException;

class Collection implements IO {

    protected int $collectionSize = 0;
    protected array $values = [];

    public function __construct(
            private readonly ?string $collectionType
    ) {
        
    }

    public function exists(string $name): bool {
        return array_key_exists($name, $this->values);
    }

    public function get(string $name): mixed {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }

        throw new RuntimeException("Could not find collection name/key: $name");
    }

    public function getAll(): Generator {
        foreach ($this->values as $k => $v) {
            yield $k => $v;
        }
    }

    public function getAllScalar(): Generator {
        foreach ($this->values as $k => $v) {
            if (is_scalar($v)) {
                yield $k => $v;
            }
        }
    }

    public function getAllTyped(string $type): Generator {
        foreach ($this->values as $k => $v) {
            if ($v instanceof $type) {
                yield $k => $v;
            }
        }
    }

    public function getSize(): int {
        return $this->collectionSize;
    }

    public function isTypedCollection(): bool {
        return empty($this->collectionType);
    }

    public function set(
            mixed $value,
            ?string $name = null
    ): self {
        if ($this->collectionType) {
            if (!($value instanceof $this->collectionType)) {
                throw new LogicException('$value does not match collection allowed type: ' . $this->collectionType);
            }
            if (array_key_exists($name ?? get_class($value), $this->values)) {
                if (is_array($name ?? get_class($value))) {
                    $this->values[$name ?? get_class($value)][] = $value;
                } else {
                    $temp = $this->values[$name ?? get_class($value)];
                    $this->values[$name ?? get_class($value)] = [
                        $temp,
                        $value
                    ];
                }
            } else {
                $this->values[$name ?? get_class($value)] = $value;
            }

            $this->collectionSize++;
        } else {
            $this->values[$name ?? $this->collectionSize++] = $value;
        }

        return $this;
    }

    public function toArray(): array {
        return $this->values;
    }
}
