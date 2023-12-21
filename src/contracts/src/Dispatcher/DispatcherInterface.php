<?php

declare(strict_types=1);

namespace Larmias\Contracts\Dispatcher;

interface DispatcherInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function dispatch(array $params = []): mixed;
}