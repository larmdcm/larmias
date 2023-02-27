<?php

declare(strict_types=1);

namespace Larmias\View;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ViewInterface;

class View implements ViewInterface
{
    /**
     * @var ViewInterface
     */
    protected ViewInterface $driver;

    /**
     * View constructor.
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        $config = $this->getConfig();
        /** @var ViewInterface $driver */
        $driver = $this->container->make($config['driver'], ['config' => $config]);
        $this->driver = $driver;
    }

    /**
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(array|string $name, mixed $value = null): ViewInterface
    {
        return $this->driver->with($name, $value);
    }

    /**
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string
    {
        return $this->driver->render($path, $vars);
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
            return $this->config->get('view');
        }
        return $this->config->get('view.' . $name, $default);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (\str_contains($name, 'with')) {
            return $this->driver->with(\lcfirst(\substr($name, -(\strlen($name) - 4))), $arguments[0]);
        }
        return $this->driver->{$name}(...$arguments);
    }
}