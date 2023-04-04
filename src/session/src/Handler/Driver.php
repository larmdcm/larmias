<?php

declare(strict_types=1);

namespace Larmias\Session\Handler;

use Larmias\Contracts\ContainerInterface;
use SessionHandlerInterface;
use function array_merge;
use function method_exists;

abstract class Driver implements SessionHandlerInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }
}