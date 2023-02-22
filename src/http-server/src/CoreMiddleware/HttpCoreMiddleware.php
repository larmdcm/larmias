<?php

declare(strict_types=1);

namespace Larmias\HttpServer\CoreMiddleware;

class HttpCoreMiddleware extends CoreMiddleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'http';
}