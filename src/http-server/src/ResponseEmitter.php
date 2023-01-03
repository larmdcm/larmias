<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\Http\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;

class ResponseEmitter implements ResponseEmitterInterface
{
    public function emit(PsrResponseInterface $response, ResponseInterface $serverResponse, bool $withContent = true)
    {
        $content = $response->getBody();
        $serverResponse = $serverResponse->withHeaders($response->getHeaders())
            ->status($response->getStatusCode(), $response->getReasonPhrase());
        if ($withContent) {
            $serverResponse->end((string)$content);
        } else {
            $serverResponse->end();
        }
    }
}