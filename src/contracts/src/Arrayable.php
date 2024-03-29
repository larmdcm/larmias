<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface Arrayable
{
    /**
     * @return array
     */
    public function toArray(): array;
}