<?php

declare(strict_types=1);

namespace Larmias\Routing\Dispatchers;

use Larmias\Routing\Dispatcher;

class Callback extends Dispatcher
{
    /**
     * @param array $params
     * @return mixed
     */
    public function execute(array $params = []): mixed
    {
        return $this->container->invoke($this->rule->getHandler(), $params);
    }
}