<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    public function getPathInfo(): string;
}