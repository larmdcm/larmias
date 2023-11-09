<?php

declare(strict_types=1);

namespace Larmias\Routing\Dispatchers;

use Larmias\Routing\Dispatcher;
use RuntimeException;
use Throwable;
use function is_callable;
use function is_string;
use function explode;
use function is_array;

class Controller extends Dispatcher
{
    /**
     * @param array $params
     * @return mixed
     * @throws Throwable
     */
    public function execute(array $params = []): mixed
    {
        $option = $this->rule->getOption();
        $handler = $this->getHandler($option);
        return $this->container->invoke($handler, $params);
    }

    /**
     * @param array $option
     * @return callable
     * @throws Throwable
     */
    protected function getHandler(array $option): callable
    {
        $handler = $this->rule->getHandler();
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            $handler = explode('@', $handler);
        }

        if (!is_array($handler)) {
            throw new RuntimeException('controller handler parse error');
        }
        $className = $handler[0];
        if (is_string($className)) {
            if ($option['namespace'] !== '') {
                $className = $option['namespace'] . "\\" . $className;
            }
            $instance = $this->container->get($className);
        } else {
            $instance = $className;
        }
        return [$instance, $handler[1]];
    }
}