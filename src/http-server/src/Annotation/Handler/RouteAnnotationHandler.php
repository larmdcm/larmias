<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation\Handler;

use Larmias\Di\Contracts\AnnotationHandlerInterface;
use Larmias\HttpServer\Annotation\Controller;
use Larmias\HttpServer\Annotation\DeleteMapping;
use Larmias\HttpServer\Annotation\GetMapping;
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
    ];

    public function handle(): void
    {
        foreach (static::$container['class'] as $class => $param) {
            $routes = static::$container['routes'][$class] ?? [];
            if (empty($routes)) {
                continue;
            }
            Router::group($param->prefix, function () use ($routes) {
                foreach ($routes as $route) {
                    Router::rule($route['value']->methods, $route['value']->path, [$route['class'], $route['method']]);
                }
            })->middleware($param->middleware);
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
                static::$container['class'][$param['class']] = $param['value'];
                break;
        }
    }

    protected function collectMethod(array $param)
    {
        switch ($param['annotation']) {
            case RequestMapping::class:
            case GetMapping::class:
            case PostMapping::class:
            case DeleteMapping::class:
            case PatchMapping::class:
            case PutMapping::class:
                static::$container['routes'][$param['class']][] = $param;
                break;
        }
    }
}