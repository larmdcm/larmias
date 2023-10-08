<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Contracts;

interface ParserInterface
{
    /**
     * 解析获取token
     * @return string
     */
    public function parse(): string;
}