<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestHandlerInterface
{
    public function send(RequestInterface $request, array $options): ResponseInterface;
}