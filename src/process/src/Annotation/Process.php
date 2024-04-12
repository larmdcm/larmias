<?php

declare(strict_types=1);

namespace Larmias\Process\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Process
{
    /**
     * @param string|null $name
     * @param int|null $num
     * @param int|null $timespan
     * @param bool|null $enableCoroutine
     * @param bool $enabled
     */
    public function __construct(
        public ?string $name = null,
        public ?int    $num = 1,
        public ?int    $timespan = null,
        public ?bool   $enableCoroutine = null,
        public bool    $enabled = true,
    )
    {
    }
}