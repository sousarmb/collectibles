<?php

declare(strict_types=1);

namespace Collectibles;

use Collectibles\Contracts\IO as IOInterface;
use LogicException;

readonly class IO implements IOInterface
{
    public function get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new LogicException("Trying to get undefined IO property: $name");
    }
}
