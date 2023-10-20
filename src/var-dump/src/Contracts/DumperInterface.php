<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Contracts;

interface DumperInterface
{
    /**
     * @param ...$vars
     * @return string
     */
    public function dump(...$vars): string;
}