<?php

declare(strict_types=1);

namespace Larmias\Support;

use Larmias\Context\ApplicationContext;
use Larmias\Contracts\DotEnvInterface;
use Larmias\Support\Reflection\ReflectUtil;
use RuntimeException;
use Stringable;
use Throwable;
use Closure;

/**
 * 用容器创建对象
 * @param string $abstract
 * @param array $params
 * @param bool $newInstance
 * @return mixed
 */
function make(string $abstract, array $params = [], bool $newInstance = false): mixed
{
    if (ApplicationContext::hasContainer()) {
        return ApplicationContext::getContainer()->make($abstract, $params, $newInstance);
    }
    $params = array_values($params);
    return new $abstract(...$params);
}

/**
 * 调用反射执行callable 支持参数绑定
 * @param mixed $callable
 * @param array $params
 * @param bool $accessible
 * @return mixed
 */
function invoke(mixed $callable, array $params = [], bool $accessible = false): mixed
{
    if (ApplicationContext::hasContainer()) {
        return ApplicationContext::getContainer()->invoke($callable, $params, $accessible);
    }

    return is_callable($callable) ? $callable(...$params) : throw new RuntimeException('invoke callable is not callable.');
}

/**
 * 环境变量获取
 * @param string $name
 * @param mixed|null $default
 * @return mixed
 */
function env(string $name, mixed $default = null): mixed
{
    if (ApplicationContext::hasContainer() && ApplicationContext::getContainer()->has(DotEnvInterface::class)) {
        /** @var DotEnvInterface $dotenv */
        $dotenv = make(DotEnvInterface::class);
        return $dotenv->get($name, $default);
    }

    $value = getenv($name);
    if ($value === false) {
        return value($default);
    }
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }
    if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
        return substr($value, 1, -1);
    }
    return $value;
}

/**
 * 换行打印输出
 * @param string|Stringable $format
 * @param ...$args
 * @return void
 */
function println(string|Stringable $format = '', ...$args): void
{
    printf($format . PHP_EOL, ...$args);
}

/**
 * 格式化异常信息
 * @param Throwable $e
 * @param bool $trace
 * @return string
 */
function format_exception(Throwable $e, bool $trace = true): string
{
    $message = $e->getMessage() . sprintf(' %s(code:%d) in %s:%d', get_class($e), $e->getCode(), $e->getFile(), $e->getLine());
    if ($trace) {
        $message = $message . PHP_EOL . '[stacktrace]' . PHP_EOL . $e->getTraceAsString();
    }
    return $message;
}

/**
 * 判断值是否为空
 * @param mixed $value
 * @return boolean
 */
function is_empty(mixed $value): bool
{
    return empty($value) && !is_numeric($value);
}


/**
 * 按条件抛异常
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param  ...$parameters
 * @return void
 * @throws Throwable
 */
function throw_if(mixed $condition, Throwable|string $exception, ...$parameters): void
{
    if ($condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }
}

/**
 * 按条件抛异常
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param  ...$parameters
 * @return void
 * @throws Throwable
 */
function throw_unless(mixed $condition, Throwable|string $exception, ...$parameters): void
{
    if (!$condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }
}

/**
 * 对一个值调用给定的闭包，然后返回该值
 * @param mixed $value
 * @param callable|null $callback
 * @return mixed
 */
function tap(mixed $value, callable $callback = null): mixed
{
    if (is_null($callback)) {
        return $value;
    }

    $callback($value);

    return $value;
}

/**
 * Return the default value of the given value.
 *
 * @param mixed $value
 * @return mixed
 */
function value(mixed $value): mixed
{
    return $value instanceof Closure ? $value() : $value;
}

/**
 * 获取类名(不包含命名空间)
 * @param mixed $class
 * @return string
 */
function class_basename(mixed $class): string
{
    $class = is_object($class) ? get_class($class) : $class;
    return basename(str_replace('\\', '/', $class));
}

/**
 * 类是否实现了某个接口
 * @param mixed $class
 * @param mixed $interface
 * @return bool
 */
function class_has_implement(mixed $class, string $interface): bool
{
    return ReflectUtil::classHasImplement($class, $interface);
}

/**
 * 获取CPU核心数
 * @return int
 */
function get_cpu_num(): int
{
    return System::getCpuCoresNum();
}

/**
 * 判断是否为UNIX系统
 * @return bool
 */
function is_unix(): bool
{
    return System::isUnix();
}