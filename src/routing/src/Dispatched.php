<?php

declare(strict_types=1);

namespace Larmias\Routing;

class Dispatched
{
    /**
     * Result constructor.
     *
     * @param Dispatcher $dispatcher
     * @param Rule $rule
     * @param array $params
     */
    public function __construct(public Dispatcher $dispatcher,public Rule $rule,public array $params = [])
    {
    }
}