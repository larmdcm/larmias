<?php

declare(strict_types=1);

namespace Larmias\Routing;

use Larmias\Contracts\ContainerInterface;
use Closure;
use Larmias\Routing\Dispatchers\Callback;
use Larmias\Routing\Dispatchers\Controller;

abstract class Dispatcher
{
    /**
     * Dispatch constructor.
     *
     * @param ContainerInterface $container
     * @param Rule $rule
     */
    public function __construct(protected ContainerInterface $container, protected Rule $rule)
    {
    }

    /**
     * @param ContainerInterface $container
     * @param Rule $rule
     * @return Dispatcher
     */
    public static function create(ContainerInterface $container, Rule $rule): Dispatcher
    {
        $handler = $rule->getHandler();
        if ($handler instanceof Closure) {
            return new Callback($container, $rule);
        }
        return new Controller($container, $rule);
    }

    /**
     * @return mixed
     */
    abstract protected function execute(array $params = []): mixed;

    /**
     * @return mixed
     */
    public function run(array $params = []): mixed
    {
        return $this->execute($params);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }
}