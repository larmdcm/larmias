<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

use Closure;
use Larmias\Context\ApplicationContext;
use Larmias\Pipeline\Pipeline;

trait ProxyHandler
{
    /**
     * @param string $method
     * @param array $args
     * @param Closure $process
     * @return mixed
     * @throws \Throwable
     */
    protected static function __callViaProxy(string $method, array $args, Closure $process): mixed
    {
        $pipes = AspectCollector::getMethodAspects(static::class, $method);
        if (empty($pipes)) {
            return $process(...$args);
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
        return $pipeline->then(function () use ($process, $args) {
            return $process(...$args);
        });
    }
}