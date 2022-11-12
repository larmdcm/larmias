<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\Http\ResponseInterface;
use Psr\Container\ContainerInterface;

class Server
{
    /** @var string */
    public const ON_REQUEST = 'onRequest';

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param \Larmias\Contracts\Http\RequestInterface $request
     * @param \Larmias\Contracts\Http\ResponseInterface $response
     */
    public function onRequest(RequestInterface $request,ResponseInterface $response): void
    {
        $response->end(__FUNCTION__);
    }
}