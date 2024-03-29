<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ProtocolInterface extends PackerInterface
{
    /**
     * @param mixed $data
     * @return int
     */
    public function input(mixed $data): int;
}