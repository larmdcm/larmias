<?php

declare(strict_types=1);

namespace Larmias\Routing;

class Dispatched
{
    /**
     * Result constructor.
     *
     * @param \Larmias\Routing\Dispatcher $dispatcher
     * @param \Larmias\Routing\Rule $rule
     * @param array $params
     */
    public function __construct(public Dispatcher $dispatcher,public Rule $rule,public array $params = [])
    {
    }
}