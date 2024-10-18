<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Contracts\Http\ResponseInterface;

interface SseEmitterInterface
{
    public function emit(ResponseInterface $response): void;
}