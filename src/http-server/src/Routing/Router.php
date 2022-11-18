<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Routing;

use Larmias\Contracts\ContainerInterface;
use Larmias\Routing\Router as RouteCollector;

/**
 * @method static RouteCollector rule(string|array $method, string $route, mixed $handler)
 * @method static RouteCollector group(callable|array|string $option, ?callable $handler = null)
 * @method static RouteCollector prefix(string $prefix)
 * @method static RouteCollector middleware(string|array $middleware)
 * @method static RouteCollector namespace(string $namespace)
 */
class Router
{
    /**
     * @var RouteCollector|null
     */
    protected static ?RouteCollector $router = null;

    /**
     * @param \Larmias\Contracts\ContainerInterface $container
     * @return void
     */
    public static function init(ContainerInterface $container): void
    {
        static::$router = new RouteCollector($container);
    }

    /**
     * Adds a GET route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function get(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function post(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function put(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function delete(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function patch(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return \Larmias\Routing\Router
     */
    public static function head(string $route, mixed $handler): RouteCollector
    {
        return static::$router->rule('HEAD', $route, $handler);
    }

    /**
     * @return \Larmias\Routing\Router
     */
    public static function getRouteCollector(): RouteCollector
    {
        return static::$router;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::$router->{$name}(...$arguments);
    }
}