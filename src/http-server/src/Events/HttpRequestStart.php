<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Events;

use Psr\Http\Message\ServerRequestInterface;

class HttpRequestStart
{
    public function __construct(public ServerRequestInterface $request)
    {
    }
}