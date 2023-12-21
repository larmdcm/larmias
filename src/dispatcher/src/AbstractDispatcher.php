<?php

declare(strict_types=1);

namespace Larmias\Dispatcher;

use Closure;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Dispatcher\DispatcherInterface;
use Larmias\Dispatcher\Dispatchers\Callback;
use Larmias\Dispatcher\Dispatchers\Controller;
use Larmias\Contracts\Dispatcher\RuleInterface;

abstract class AbstractDispatcher implements DispatcherInterface
{
    /**
     * Dispatch constructor.
     * @param ContainerInterface $container
     * @param RuleInterface $rule
     */
    public function __construct(protected ContainerInterface $container, protected RuleInterface $rule)
    {
    }

    /**
     * @param ContainerInterface $container
     * @param RuleInterface $rule
     * @return AbstractDispatcher
     */
    public static function make(ContainerInterface $container, RuleInterface $rule): AbstractDispatcher
    {
        $handler = $rule->getHandler();
        if ($handler instanceof Closure) {
            return new Callback($container, $rule);
        }
        return new Controller($container, $rule);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return RuleInterface
     */
    public function getRule(): RuleInterface
    {
        return $this->rule;
    }
}