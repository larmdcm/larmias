<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Server\ServerItemConfig;
use Psr\Container\ContainerInterface;

class Server
{
    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     * @param ServerItemConfig $config
     */
    public function __construct(protected ContainerInterface $container,protected ServerItemConfig $config)
    {
    }

    public function onRequest()
    {
    }
}