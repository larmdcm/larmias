<?php

declare(strict_types=1);

namespace Larmias\Dispatcher\Dispatchers;

use Larmias\Dispatcher\AbstractDispatcher;

class Callback extends AbstractDispatcher
{
    /**
     * @param array $params
     * @return mixed
     */
    public function dispatch(array $params = []): mixed
    {
        return $this->container->invoke($this->rule->getHandler(), $params);
    }
}