<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Di\AnnotationManager;
use Larmias\HttpServer\Annotation\Controller;
use Larmias\HttpServer\Annotation\DeleteMapping;
use Larmias\HttpServer\Annotation\GetMapping;
use Larmias\HttpServer\Annotation\Handler\RouteAnnotationHandler;
use Larmias\HttpServer\Annotation\Handler\RouteAnnotationHandlerInterface;
use Larmias\HttpServer\Annotation\Middleware;
use Larmias\HttpServer\Annotation\PatchMapping;
use Larmias\HttpServer\Annotation\PostMapping;
use Larmias\HttpServer\Annotation\PutMapping;
use Larmias\HttpServer\Annotation\RequestMapping;
use \Larmias\Routing\Router as BaseRouter;
use Larmias\HttpServer\Routing\Router;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function register(): void
    {
        /** @var BaseRouter $router */
        $router = $this->container->make(BaseRouter::class);
        Router::init($router);

        if (!$this->container->has(RouteAnnotationHandlerInterface::class)) {
            $this->container->bind(RouteAnnotationHandlerInterface::class, RouteAnnotationHandler::class);
        }

        AnnotationManager::addHandler([
            Controller::class,
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            DeleteMapping::class,
            PatchMapping::class,
            PutMapping::class,
            Middleware::class,
        ], RouteAnnotationHandlerInterface::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}