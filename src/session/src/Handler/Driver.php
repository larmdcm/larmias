<?php

declare(strict_types=1);

namespace Larmias\Session\Handler;

use Larmias\Contracts\ContainerInterface;
use SessionHandlerInterface;

abstract class Driver implements SessionHandlerInterface
{
    protected array $config = [];

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);

        if (\method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }
}