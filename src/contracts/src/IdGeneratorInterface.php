<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface IdGeneratorInterface
{
    /**
     * 生成id.
     *
     * @return string
     */
    public function id(): string;
}