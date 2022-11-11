<?php

declare(strict_types=1);

namespace Larmias\Engine;

class DriverConfig
{
    /** @var string|null */
    protected ?string $driver;

    /** @var string|null */
    protected ?string $httpServer;

    /**
     * @param array $config
     * @return DriverConfig
     */
    public static function build(array $config = []): DriverConfig
    {
        $driverConfig = new DriverConfig();
        if (isset($config['driver'])) {
            $driverConfig->setDriver($config['driver']);
        }
        if (isset($config['http_server'])) {
            $driverConfig->setHttpServer($config['http_server']);
        }
        return $driverConfig;
    }

    /**
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver;
    }

    /**
     * @param string|null $driver
     * @return self
     */
    public function setDriver(?string $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpServer(): ?string
    {
        return $this->httpServer;
    }

    /**
     * @param string|null $httpServer
     */
    public function setHttpServer(?string $httpServer): self
    {
        $this->httpServer = $httpServer;
        return $this;
    }
}