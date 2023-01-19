<?php

declare(strict_types=1);

namespace Larmias\Session;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\SessionInterface;
use SessionHandlerInterface;

class Session implements SessionInterface
{
    /**
     * @var SessionHandlerInterface
     */
    protected ?SessionHandlerInterface $handler = null;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var string
     */
    protected string $name = 'PHPSESSID';

    /**
     * @var string
     */
    protected string $id;

    /**
     * Session constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        $this->setId();
    }

    /**
     * 设置sessionId
     *
     * @param string|null $id
     * @return void
     */
    public function setId(?string $id = null): void
    {
        $this->id = \is_string($id) && \strlen($id) === 32 && \ctype_alnum($id) ? $id : \md5(\microtime(true) . \session_create_id());
    }

    /**
     * 设置SessionName
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * 获取sessionName
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取sessionId
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * 获取session handler
     *
     * @return SessionHandlerInterface
     */
    public function handler(): SessionHandlerInterface
    {
        if ($this->handler instanceof SessionHandlerInterface) {
            return $this->handler;
        }
        $handler = $this->getConfig('handler');
        $handler = $this->container->make($handler, ['config' => $this->getConfig()]);
        /** @var SessionHandlerInterface $handler */
        return $this->handler = $handler;
    }

    /**
     * 获取配置.
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if (\is_null($name)) {
            return $this->config->get('session');
        }
        return $this->config->get('session.' . $name, $default);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->driver()->{$name}(...$arguments);
    }
}