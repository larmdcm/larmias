<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string;

    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(string $data): mixed;
}