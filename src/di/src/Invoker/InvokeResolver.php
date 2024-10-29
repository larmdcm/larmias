<?php

declare(strict_types=1);

namespace Larmias\Di\Invoker;

use Closure;
use Larmias\Context\ApplicationContext;
use Larmias\Di\Annotation\Invoke;
use Larmias\Di\Aop\AspectCollector;
use Larmias\Di\Aop\JoinPoint;
use Larmias\Pipeline\Pipeline;
use function array_map;
use function call_user_func;

class InvokeResolver
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param array $param
     * @return void
     */
    public static function collect(array $param): void
    {
        if ($param['annotation'] !== Invoke::class) {
            return;
        }

        AspectCollector::parse($param, function (array $params) {
            ['class' => $class, 'method' => $method, 'aspectHandler' => $aspectHandler] = $params;
            if (isset(static::$container[$class]['*'])) {
                $method = '*';
            }
            static::$container[$class][$method][] = $aspectHandler;
        });
    }

    /**
     * @return bool
     */
    public static function isEmpty(): bool
    {
        return empty(static::$container);
    }

    /**
     * @param string $class
     * @param string $method
     * @return array
     * @throws \Throwable
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        $aspects = static::$container[$class][$method] ?? [];
        if (!empty(static::$container[$class]['*'])) {
            $aspects = array_merge($aspects, static::$container[$class]['*']);
        }

        return array_values(array_unique($aspects));
    }

    /**
     * @param Closure $process
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public static function process(Closure $process, array $args): mixed
    {
        $pipes = static::getMethodAspects($args['class'], $args['method']);
        if (empty($pipes)) {
            return $process();
        }
        $pipeline = new Pipeline();
        $pipeline->through(
            array_map(function ($handler) {
                return function ($param, $next) use ($handler) {
                    $handler = ApplicationContext::hasContainer() ? ApplicationContext::getContainer()->get($handler) : new $handler;
                    return call_user_func([$handler, 'process'], new JoinPoint($next, $param));
                };
            }, $pipes)
        );
        return $pipeline->then(function () use ($process) {
            return $process();
        });
    }
}