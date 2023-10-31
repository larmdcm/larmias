<?php

declare(strict_types=1);

namespace Larmias\Session;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\PackerInterface;
use Larmias\Contracts\SessionInterface;
use SessionHandlerInterface;
use Larmias\Collection\Arr;
use function is_null;
use function is_string;
use function strlen;
use function ctype_alnum;
use function md5;
use function microtime;
use function session_create_id;

class Session implements SessionInterface
{
    /**
     * @var SessionHandlerInterface
     */
    protected ?SessionHandlerInterface $handler = null;

    /**
     * @var PackerInterface
     */
    protected PackerInterface $packer;

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
     * @var bool
     */
    protected bool $started = false;

    /**
     * Session constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        /** @var PackerInterface $packer */
        $packer = $this->container->make($this->getConfig('packer'));
        $this->packer = $packer;
        $this->setId($this->generateSessionId());
        $this->setName($this->getConfig('name'));
        $this->handler = $this->getHandler();
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        $data = $this->handler->read($this->getId());
        if (!empty($data)) {
            $this->data = $this->packer->unpack($data);
        } else {
            $this->data = [];
        }
        return $this->started = true;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if (!empty($this->data)) {
            $this->handler->write($this->getId(), $this->packer->pack($this->data));
        } else {
            $this->handler->destroy($this->getId());
        }
        $this->started = false;
        return true;
    }

    /**
     * 获取全部session数据
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * 设置session数据
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, mixed $value): bool
    {
        Arr::set($this->data, $name, $value);
        return true;
    }

    /**
     * 获取session数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return Arr::get($this->data, $name, $default);
    }

    /**
     * 获取session并删除
     *
     * @param string $name
     * @return mixed
     */
    public function pull(string $name): mixed
    {
        return Arr::pull($this->data, $name);
    }

    /**
     * 添加数据到一个session数组
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function push(string $key, mixed $value): bool
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->set($key, $array);
        return true;
    }

    /**
     * 判断session数据是否存在
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return Arr::has($this->data, $name);
    }

    /**
     * 删除session数据
     *
     * @param string $name
     * @return bool
     */
    public function delete(string $name): bool
    {
        Arr::forget($this->data, $name);
        return true;
    }

    /**
     * 清空session数据
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->data = [];
        return true;
    }

    /**
     * 销毁session
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->clear();

        $this->regenerate(true);
    }

    /**
     * 重新生成session id
     *
     * @param bool $destroy
     * @return void
     */
    public function regenerate(bool $destroy = false): void
    {
        if ($destroy) {
            $this->handler->destroy($this->getId());
        }

        $this->setId($this->generateSessionId());
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * 设置sessionId
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
     * @param string|null $id
     * @return bool
     */
    public function validId(?string $id = null): bool
    {
        return is_string($id) && strlen($id) === 32 && ctype_alnum($id);
    }

    /**
     * @return string
     */
    public function generateSessionId(): string
    {
        return md5(microtime(true) . session_create_id());
    }

    /**
     * 获取session handler
     *
     * @return SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface
    {
        $default = $this->getConfig('default', 'file');
        $handlerConfig = $this->getConfig('handlers.' . $default, []);
        /** @var SessionHandlerInterface $handler */
        $handler = $this->container->make($handlerConfig['handler'], ['config' => $handlerConfig]);
        return $handler;
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
        if (is_null($name)) {
            return $this->config->get('session');
        }
        return $this->config->get('session.' . $name, $default);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->handler->{$name}(...$arguments);
    }
}