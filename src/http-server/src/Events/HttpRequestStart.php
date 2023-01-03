<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Events;

use Larmias\HttpServer\Contracts\RequestInterface;

class HttpRequestStart
{
    public function __construct(public RequestInterface $request)
    {
    }
}