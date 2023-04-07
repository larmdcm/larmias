<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface PaginatorInterface
{
    /**
     * @return string
     */
    public function render(): string;
}