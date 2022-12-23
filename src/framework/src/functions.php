<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Di\Container;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;

/**
 * make
 *
 * @param string $abstract
 * @param array $params
 * @param bool $newInstance
 * @return object
 */
function make(string $abstract, array $params = [], bool $newInstance = false): object
{
    return Container::getInstance()->make($abstract, $params, $newInstance);
}

/**
 * App辅助函数
 *
 * @return ApplicationInterface
 */
function app(): ApplicationInterface
{
    /** @var ApplicationInterface $app */
    $app = make(ApplicationInterface::class);
    return $app;
}

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
    $config = make(ConfigInterface::class);
    if ($key === null && $value === null) {
        return $config;
    }
    if (\is_array($key)) {
        return $config->set($key);
    }
    return \str_starts_with($key, '?') ? $config->has($key) : $config->get($key, $value);
}