<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Routing;

use Larmias\Routing\Contracts\RouterInterface;
use Larmias\Routing\Rule;
use Larmias\Routing\Dispatched;

/**
 * @method static RouterInterface rule(string|array $method, string $route, mixed $handler)
 * @method static RouterInterface group(callable|array|string $option, ?callable $handler = null)
 * @method static RouterInterface prefix(string $prefix)
 * @method static RouterInterface middleware(string|array $middleware)
 * @method static RouterInterface namespace(string $namespace)
 * @method static Rule[] getRules()
 * @method static Rule|null getRule(string|int $name)
 * @method static Dispatched dispatch(string $method, string $route)
 */
class Router
{
    /**
     * @var RouterInterface
     */
    protected static RouterInterface $router;

    /**
     * @param RouterInterface $router
     * @return void
     */
    public static function init(RouterInterface $router): void
    {
        static::$router = $router;
    }

    /**
     * Adds a GET route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function get(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function post(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function put(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function delete(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function patch(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouterInterface
     */
    public static function head(string $route, mixed $handler): RouterInterface
    {
        return static::$router->rule('HEAD', $route, $handler);
    }

    /**
     * @return RouterInterface
     */
    public static function getRouter(): RouterInterface
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