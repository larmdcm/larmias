<?php

declare(strict_types=1);

namespace Larmias\Process\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Process
{
    /**
     * @param string|null $name
     * @param int|string|null $num
     * @param int|string|null $timespan
     * @param bool|string|null $enableCoroutine
     * @param bool|string|null $enabled
     */
    public function __construct(
        public ?string          $name = null,
        public int|string|null  $num = 1,
        public int|string|null  $timespan = null,
        public bool|string|null $enableCoroutine = null,
        public bool|string|null $enabled = true,
    )
    {
    }
}