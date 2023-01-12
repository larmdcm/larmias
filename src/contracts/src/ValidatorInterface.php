<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ValidatorInterface
{
    public function validated(): array;

    public function fails(): bool;

    public function errors(): array;
}