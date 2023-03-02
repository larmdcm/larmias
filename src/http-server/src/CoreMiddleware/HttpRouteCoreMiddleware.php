w<?php

declare(strict_types=1);

namespace Larmias\HttpServer\CoreMiddleware;

class HttpRouteCoreMiddleware extends CoreMiddleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'http_route';
}