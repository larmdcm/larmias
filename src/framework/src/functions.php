<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\DotEnvInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Di\Container;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Psr\Log\LogLevel;

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

/**
 * 翻译
 *
 * @param string $key
 * @param array $vars
 * @param string|null $locale
 * @return string
 */
function trans(string $key, array $vars = [], ?string $locale = null): string
{
    /** @var TranslatorInterface $translator */
    $translator = make(TranslatorInterface::class);
    return $translator->trans($key, $vars, $locale);
}

/**
 * 获取日志操作对象
 *
 * @return LoggerInterface
 */
function logger(): LoggerInterface
{
    /** @var LoggerInterface $logger */
    $logger = make(LoggerInterface::class);
    return $logger;
}

/**
 * 日志记录.
 *
 * @param mixed $message
 * @param string $level
 * @param array $context
 * @return void
 */
function trace(mixed $message, string $level = LogLevel::INFO, array $context = []): void
{
    $logger = logger();
    $logger->log($level, \is_scalar($message) ? $message : (string)\var_export($message, true), $context);
}

/**
 * 获取环境变量
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