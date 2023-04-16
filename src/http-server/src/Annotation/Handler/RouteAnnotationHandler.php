<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation\Handler;

use Larmias\HttpServer\Annotation\Controller;
use Larmias\HttpServer\Annotation\DeleteMapping;
use Larmias\HttpServer\Annotation\GetMapping;
use Larmias\HttpServer\Annotation\Mapping;
use Larmias\HttpServer\Annotation\Middleware;
use Larmias\HttpServer\Annotation\PatchMapping;
use Larmias\HttpServer\Annotation\PostMapping;
use Larmias\HttpServer\Annotation\PutMapping;
use Larmias\HttpServer\Annotation\RequestMapping;
use Larmias\HttpServer\Routing\Router;

class RouteAnnotationHandler implements RouteAnnotationHandlerInterface
{
    /**
     * @var array|array[]
     */
    protected static array $container = [
        'controller' => [],
        'routes' => [],
        'middleware' => [],
        'method_middleware' => [],
    ];

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (static::$container['controller'] as $class => $controller) {
            $routes = static::$container['routes'][$class] ?? [];
            if (empty($routes)) {
                continue;
            }
            Router::group($this->buildControllerPrefix($controller->prefix), function () use ($routes) {
                foreach ($routes as $route) {
                    $value = $route['value'][0];
                    $router = Router::rule($value->methods, $value->path, [$route['class'], $route['method']])
                        ->middleware($this->buildMiddleware(static::$container['method_middleware'][$route['class']][$route['method']] ?? []));
                    if (isset($value->options['name'])) {
                        $router->name($value->options['name']);
                    }
                }
            })->middleware($controller->middleware)->middleware($this->buildMiddleware(static::$container['middleware'][$class] ?? []));
        }
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function buildControllerPrefix(string $prefix): string
    {
        return $prefix;
    }


    /**
     * @param array $param
     * @return void
     */
    public function collect(array $param): void
    {
        switch ($param['type']) {
            case 'class':
                $this->collectClass($param);
                break;
            case 'method':
                $this->collectMethod($param);
                break;
        }
    }

    /**
     * @param array $param
     * @return void
     */
    protected function collectClass(array $param)
    {
        switch ($param['annotation']) {
            case Controller::class:
                static::$container['controller'][$param['class']] = $param['value'][0];
                break;
            case Middleware::class:
                static::$container['middleware'][$param['class']] = $param['value'];
                break;
        }
    }

    /**
     * @param array $param
     * @return void
     */
    protected function collectMethod(array $param)
    {
        switch ($param['annotation']) {
            case Mapping::class:
            case RequestMapping::class:
            case GetMapping::class:
            case PostMapping::class:
            case DeleteMapping::class:
            case PatchMapping::class:
            case PutMapping::class:
                static::$container['routes'][$param['class']][] = $param;
                break;
            case Middleware::class:
                static::$container['method_middleware'][$param['class']][$param['method']] = $param['value'];
                break;
        }
    }

    /**
     * @param array $middleware
     * @return array
     */
    protected function buildMiddleware(array $middleware): array
    {
        $result = [];
        foreach ($middleware as $item) {
            $result = \array_merge($result, $item->middleware);
        }
        return $result;
    }
}