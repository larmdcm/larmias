<?php

declare(strict_types=1);

namespace Larmias\Trace\Contracts;

interface TraceContextInterface
{
    /**
     * @return TraceInterface
     */
    public function getContextForTrace(): TraceInterface;
}