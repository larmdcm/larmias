<?php

declare(strict_types=1);

namespace Larmias\Utils\Contracts;

interface Arrayable
{
    /**
     * @return array
     */
    public function toArray(): array;
}