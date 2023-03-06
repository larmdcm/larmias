<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Contracts\WorkerConfigInterface;

class WorkerConfig implements WorkerConfigInterface
{
    /** @var string  */
    protected string $name;

    /** @var string|null  */
    protected ?string $host;

    /** @var int|null  */
    protected ?int $port;

    /** @var int  */
    protected int $type;

    /** @var array */
    protected array $settings = [];

    /** @var array  */
    protected array $callbacks = [];

    /**
     * ServerItemConfig constructor.
     *
     * @param array $config
     */
    public function __construct(protected array $config = [])
    {
        $this->setName($this->config['name'] ?? '')
             ->setHost($this->config['host'] ?? null)
             ->setPort($this->config['port'] ?? null)
             ->setType($this->config['type'] ?? WorkerType::TCP_SERVER)
             ->setSettings($this->config['settings'] ?? [])
             ->setCallbacks($this->config['callbacks'] ?? []);
    }

    /**
     * @param array $config
     * @return WorkerConfig
     */
    public static function build(array $config = []): WorkerConfig
    {
        return new static($config);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return self
     */
    public function setHost(?string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return self
     */
    public function setPort(?int $port): self
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = $type;
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