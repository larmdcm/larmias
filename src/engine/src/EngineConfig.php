<?php

declare(strict_types=1);

namespace Larmias\Engine;

use RuntimeException;

class EngineConfig
{
    /** @var string */
    protected string $driver;

    /** @var WorkerConfig[] */
    protected array $workers = [];

    /** @var array */
    protected array $settings = [];

    /** @var array */
    protected array $callbacks = [];

    /** @var array */
    protected array $watch = [];
    
    /**
     * ServerConfig constructor.
     *
     * @param array $config
     */
    public function __construct(protected array $config = [])
    {
        $this->initConfig();
    }

    /**
     * @param array $config
     * @return EngineConfig
     */
    public static function build(array $config = []): EngineConfig
    {
        return new EngineConfig($config);
    }

    /**
     * @return void
     */
    protected function initConfig(): void
    {
        if (!isset($this->config['driver'])) {
            throw new RuntimeException('Engine driver not set');
        }
        $this->setDriver($this->config['driver'])
            ->setWorkers($this->config['workers'] ?? [])
            ->setSettings($this->config['settings'] ?? [])
            ->setCallbacks($this->config['callbacks'] ?? []);
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     * @return self
     */
    public function setDriver(string $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return WorkerConfig[]
     */
    public function getWorkers(): array
    {
        return $this->workers;
    }

    /**
     * @param array $workers
     * @return self
     */
    public function setWorkers(array $workers): self
    {
        foreach ($workers as $worker) {
            $this->workers[] = $worker instanceof WorkerConfig ? $worker : WorkerConfig::build($worker);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return self
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @param array $callbacks
     * @return self
     */
    public function setCallbacks(array $callbacks): self
    {
        $this->callbacks = $callbacks;
        return $this;
    }
}