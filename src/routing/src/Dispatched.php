<?php

declare(strict_types=1);

namespace Larmias\Routing;

use Larmias\Contracts\Dispatcher\DispatcherInterface;

class Dispatched
{
    /**
     * Result constructor.
     * @param DispatcherInterface $dispatcher
     * @param Rule $rule
     * @param array $params
     */
    public function __construct(public DispatcherInterface $dispatcher, public Rule $rule, public array $params = [])
    {
    }
}