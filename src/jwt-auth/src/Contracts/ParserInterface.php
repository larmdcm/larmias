<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Contracts;

interface ParserInterface
{
    /**
     * @return string
     */
    public function parse(): string;
}