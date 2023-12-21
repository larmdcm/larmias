<?php

declare(strict_types=1);

namespace Larmias\Dispatcher;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Dispatcher\DispatcherFactoryInterface;
use Larmias\Contracts\Dispatcher\DispatcherInterface;
use Larmias\Contracts\Dispatcher\RuleInterface;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param RuleInterface $rule
     * @return DispatcherInterface
     */
    public function make(RuleInterface $rule): DispatcherInterface
    {
        return AbstractDispatcher::make($this->container, $rule);
    }
}