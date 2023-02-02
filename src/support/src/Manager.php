<?php

declare(strict_types=1);

namespace Larmias\Support;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Utils\Str;
use InvalidArgumentException;

abstract class Manager
{
    /**
     * @var array
     */
    protected array $drivers = [];

    /**
     * @var string|null
     */
    protected ?string $namespace = null;

    /**
     * Manager constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * 获取默认驱动
     *
     * @return string
     */
    abstract function getDefaultDriver(): string;

    /**
     * 获取驱动实例
     *
     * @param string|null $driver
     * @return object
     */
    public function driver(?string $driver = null): object
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }
        return $this->drivers[$driver];
    }

    /**
     * 创建驱动实例
     *
     * @param string $driver
     * @return object
     */
    protected function createDriver(string $driver): object
    {
        $driver = $this->resolveType($driver);
        $method = 'create' . Str::studly($driver) . 'Driver';
        $params = $this->resolveParams($driver);
        if (method_exists($this, $method)) {
            return $this->$method(...$params);
        }

        $class = $this->resolveClass($driver);

        return $this->container->make($class, $params, true);
    }

    /**
     * 获取驱动类型
     * @param string $name
     * @return string
     */
    protected function resolveType(string $name): string
    {
        return $name;
    }

    /**
     * 获取实例配置
     *
     * @param string $driver
     * @return mixed
     */
    protected function resolveConfig(string $driver): mixed
    {
        return $driver;
    }

    /**
     * 获取驱动参数
     *
     * @param string $driver
     * @return array
     */
    protected function resolveParams(string $driver): array
    {
        $config = $this->resolveConfig($driver);
        return [$config];
    }

    /**
     * 获取驱动类
     *
     * @param string $driver
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function resolveClass(string $driver): string
    {
        if (!empty($this->namespace) || str_contains($driver, '\\')) {
            $class = str_contains($driver, '\\') ? $driver : $this->namespace . Str::studly($driver);
            if (class_exists($class)) {
                return $class;
            }
        }
        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * 移除一个驱动实例
     *
     * @param array|string|null $name
     * @return self
     */
    public function forgetDriver(string|array $name = null): self
    {
        $name = $name ?? $this->getDefaultDriver();

        foreach ((array)$name as $cacheName) {
            if (isset($this->drivers[$cacheName])) {
                unset($this->drivers[$cacheName]);
            }
        }

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Manager __call
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}