<?php

declare(strict_types=1);

namespace Collectibles\Contracts;

interface IO
{
    public function get(string $name): mixed;
}
