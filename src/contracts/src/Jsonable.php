<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use const JSON_UNESCAPED_UNICODE;

interface Jsonable
{
    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string;
}