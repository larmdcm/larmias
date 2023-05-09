<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\DotEnvInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Utils\ApplicationContext;
use function is_array;
use function str_starts_with;

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
    return ApplicationContext::getContainer()->make($abstract, $params, $newInstance);
}

/**
 * 获取app实例对象w
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
 * 框架配置操作
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
    if (is_array($key)) {
        return $config->set($key);
    }
    return str_starts_with($key, '?') ? $config->has($key) : $config->get($key, $value);
}

/**
 * 环境变量操作
 *
 * @param string $name
 * @param mixed|null $default
 * @return mixed
 */
function env(string $name, mixed $default = null): mixed
{
    /** @var DotEnvInterface $dotenv */
    $dotenv = make(DotEnvInterface::class);
    return $dotenv->get($name, $default);
}

/**
 * 获取控制台输出实例
 *
 * @return StdoutLoggerInterface
 */
function console(): StdoutLoggerInterface
{
    /** @var StdoutLoggerInterface $console */
    $console = make(StdoutLoggerInterface::class);
    return $console;
}