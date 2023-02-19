<?php

declare(strict_types=1);

namespace Larmias\Engine\Events;

use Larmias\Engine\Contracts\KernelInterface;

class BeforeStart
{
    /**
     * @param KernelInterface $kernel
     */
    public function __construct(public KernelInterface $kernel)
    {
    }
}