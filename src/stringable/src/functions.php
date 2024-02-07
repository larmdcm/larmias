<?php

declare(strict_types=1);

namespace Larmias\Stringable;


function str_buffer(string $buffer): StringBuffer
{
    return new StringBuffer($buffer);
}