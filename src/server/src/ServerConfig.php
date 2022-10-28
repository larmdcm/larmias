<?php

declare(strict_types=1);

namespace Larmias\Server;

use RuntimeException;

class ServerConfig
{
    /** @var string  */
    protected string $engine;

    /** @var array  */
    protected array $servers = [];

    /**
     * ServerConfig constructor.
     *
     * @param array $config
     */
    public function __construct(protected array $config = [])
    {
        if (!empty($this->config)) {
            $this->initConfig();
        }
    }

    /**
     * @param array $config
     * @return ServerConfig
     */
    public static function build(array $config = []): ServerConfig
    {
        return new ServerConfig($config);
    }

    /**
     * @return void
     */
    protected function initConfig(): void
    {
        if (!isset($this->config['engine'])) {
            throw new RuntimeException('Engine driver not set');
        }
        $this->engine = $this->config['engine'];
        foreach (($this->config['servers'] ?? []) as $key => $server) {
            $this->servers[$key] = ServerItemConfig::build($server);
        }
    }

    /**
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @return array
     */
    public function getServers(): array
    {
        return $this->servers;
    }
}