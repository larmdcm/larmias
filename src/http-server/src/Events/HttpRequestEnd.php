<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Events;

use Larmias\HttpServer\Contracts\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpRequestEnd
{
    public function __construct(public RequestInterface $request, public ResponseInterface $response)
    {
    }
}