<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface DataCodingInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function encode(string $data): string;

    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string;
}