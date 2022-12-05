<?php

use Larmias\Di\Container;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;

if (!function_exists('container')) {
    /**
     * 服务容器辅助函数
     *
     * @param string $abstract
     * @param array $params
     * @param bool $newInstance
     * @return object
     */
    function container(string $abstract, array $params = [], bool $newInstance = false): object
    {
        $container = Container::getInstance();
        return $abstract ? $container->make($abstract, $params, $newInstance) : $container;
    }
}

if (!function_exists('app')) {

    /**
     * App辅助函数
     *
     * @return ApplicationInterface
     */
    function app(): ApplicationInterface
    {
        /** @var ApplicationInterface $app */
        $app = container(ApplicationInterface::class);
        return $app;
    }
}

if (!function_exists('config')) {
    /**
     * 配置操作辅助函数
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    function config(mixed $key = null, mixed $value = null): mixed
    {
        /** @var ConfigInterface $config */
        $config = \container(ConfigInterface::class);
        if ($key === null && $value === null) {
            return $config;
        }
        if (\is_array($key)) {
            return $config->set($key);
        }
        return str_starts_with($key,'?') ? $config->has($key) : $config->get($key,$value);
    }
}