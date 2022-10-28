<?php

declare(strict_types=1);

namespace Larmias\Server;

class ServerItemConfig
{
    /** @var string  */
    protected string $name;

    /** @var string|null  */
    protected ?string $host;

    /** @var int|null  */
    protected ?int $port;

    /** @var string|null  */
    protected ?string $handler;

    /** @var int  */
    protected int $type;

    /**
     * ServerItemConfig constructor.
     *
     * @param array $config
     */
    public function __construct(protected array $config = [])
    {
        $this->setName($this->config['name'] ?? '');
    }

    /**
     * @param array $config
     * @return ServerItemConfig
     */
    public static function build(array $config = []): ServerItemConfig
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
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
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
     */
    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getHandler(): ?string
    {
        return $this->handler;
    }

    /**
     * @param string|null $handler
     */
    public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
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
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }
}