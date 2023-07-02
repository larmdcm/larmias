<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Events;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpRequestEnd
{
    public function __construct(public ServerRequestInterface $request, public ResponseInterface $response)
    {
    }
}