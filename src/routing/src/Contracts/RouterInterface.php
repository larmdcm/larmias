<?php

declare(strict_types=1);

namespace Larmias\Routing\Contracts;

use Larmias\Routing\Dispatched;
use Larmias\Routing\Rule;

interface RouterInterface
{
    /**
     * 添加路由规则
     *
     * @param string|array $method
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public function rule(string|array $method, string $route, mixed $handler): RouterInterface;

    /**
     * 添加路由分组
     *
     * @param callable|array|string $option
     * @param callable|null $handler
     * @return RouterInterface
     */
    public function group(callable|array|string $option, ?callable $handler = null): RouterInterface;

    /**
     * add route prefix
     *
     * @param string $prefix
     * @return RouterInterface
     */
    public function prefix(string $prefix): RouterInterface;

    /**
     * @param string $name
     * @return RouterInterface
     */
    public function name(string $name): RouterInterface;

    /**
     * add route middleware
     *
     * @param string|array $middleware
     * @return RouterInterface
     */
    public function middleware(string|array $middleware): RouterInterface;

    /**
     * add route namespace
     *
     * @param string $namespace
     * @return RouterInterface
     */
    public function namespace(string $namespace): RouterInterface;

    /**
     * @return Rule[]
     */
    public function getRules(): array;

    /**
     * @param string|int $name
     * @return Rule|null
     */
    public function getRule(string|int $name): ?Rule;

    /**
     * 路由调度.
     *
     * @param string $method
     * @param string $route
     * @return Dispatched
     */
    public function dispatch(string $method, string $route): Dispatched;
}