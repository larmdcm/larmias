<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Contracts;

interface PackerInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function pack(string $data): string;

    /**
     * @param string $data
     * @return array
     */
    public function unpack(string $data): array;
}