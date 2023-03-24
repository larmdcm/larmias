<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ValidatorInterface
{
    /**
     * @return array
     */
    public function validated(): array;

    /**
     * @return bool
     */
    public function fails(): bool;

    /**
     * @return array
     */
    public function errors(): array;
}