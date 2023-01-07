<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation\Handler;

use Larmias\Di\Contracts\AnnotationHandlerInterface;
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

class RouteAnnotationHandler implements AnnotationHandlerInterface
{
    protected static array $container = [
        'controller' => [],
        'routes' => [],
        'middlewares' => [],
        'method_middlewares' => [],
    ];

    public function handle(): void
    {
        foreach (static::$container['controller'] as $class => $controller) {
            $routes = static::$container['routes'][$class] ?? [];
            if (empty($routes)) {
                continue;
            }
            Router::group($controller->prefix, function () use ($routes) {
                foreach ($routes as $route) {
                    $value = $route['value'][0];
                    Router::rule($value->methods, $value->path, [$route['class'], $route['method']])
                        ->middleware($this->buildMiddleware(static::$container['method_middlewares'][$route['class']][$route['method']] ?? []));
                }
            })->middleware($controller->middleware)->middleware($this->buildMiddleware(static::$container['middlewares'][$class] ?? []));
        }
    }

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

    protected function collectClass(array $param)
    {
        switch ($param['annotation']) {
            case Controller::class:
                static::$container['controller'][$param['class']] = $param['value'][0];
                break;
            case Middleware::class:
                static::$container['middlewares'][$param['class']] = $param['value'];
                break;
        }
    }

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
                static::$container['method_middlewares'][$param['class']][$param['method']] = $param['value'];
                break;
        }
    }

    protected function buildMiddleware(array $middlewares): array
    {
        $result = [];
        foreach ($middlewares as $item) {
            $result = \array_merge($result, $item->middlewares);
        }
        return $result;
    }
}