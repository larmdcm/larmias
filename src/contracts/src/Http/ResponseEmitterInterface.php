<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseEmitterInterface
{
    public function emit(PsrResponseInterface $response, ResponseInterface $serverResponse, bool $withContent = true);
}