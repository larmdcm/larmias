<?php

declare(strict_types=1);

namespace Larmias\VarDumper;

use Larmias\VarDumper\Exceptions\VarDumperException;

/**
 * @param ...$vars
 * @return void
 */
function dump(...$vars): void
{
    throw new VarDumperException(...$vars);
}