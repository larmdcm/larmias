<?php

declare(strict_types=1);

namespace Larmias\Server;

use Psr\Container\ContainerInterface;

class Server
{
    /** @var ServerConfig  */
    protected ServerConfig $serverConfig;

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param ServerConfig $serverConfig
     * @return self
     */
    public function setConfig(ServerConfig $serverConfig): self
    {
        $this->serverConfig = $serverConfig;
        return $this;
    }

    public function start(): void
    {
        $servers = $this->serverConfig->getServers();
        foreach ($servers as $serverItem) {
            $this->makeServer($serverItem);
        }
        ServerEngine::run($this->serverConfig->getEngine());
    }

    /**
     * @param ServerItemConfig $config
     */
    protected function makeServer(ServerItemConfig $config)
    {
        $handler = $config->getHandler();
        if (!$handler) {
            return;
        }
        $type   = $config->getType();
        $object = new $handler($this->container,$config);
    }
}