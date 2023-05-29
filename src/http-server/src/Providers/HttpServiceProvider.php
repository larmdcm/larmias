<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;
use Larmias\Contracts\ServiceProviderInterface;
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
use Larmias\HttpServer\ResponseEmitter;
use Larmias\Routing\Router as BaseRouter;
use Larmias\HttpServer\Routing\Router;

class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        /** @var BaseRouter $router */
        $router = $this->container->make(BaseRouter::class);
        Router::init($router);

        $this->container->bindIf([
            RouteAnnotationHandlerInterface::class => RouteAnnotationHandler::class,
            ResponseEmitterInterface::class => ResponseEmitter::class,
        ]);

        if ($this->container->has(AnnotationInterface::class)) {
            /** @var AnnotationInterface $annotation */
            $annotation = $this->container->get(AnnotationInterface::class);
            $annotation->addHandler([
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
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}