<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface EncoderInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function encode(mixed $data): string;

    /**
     * @param string $data
     * @return mixed
     */
    public function decode(string $data): mixed;
}