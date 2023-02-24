<?php

declare(strict_types=1);

namespace Larmias\Process\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Process
{
    /**
     * @param string $name
     * @param int $count
     */
    public function __construct(public string $name, public int $count = 1)
    {
    }
}