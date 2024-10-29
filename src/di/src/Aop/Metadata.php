<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

class Metadata
{
    /**
     * @param string $className
     * @param bool $hasConstructor
     */
    public function __construct(
        public string $className,
        public bool   $hasConstructor = false
    )
    {
    }
}