<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseEmitterInterface
{
    /**
     * @param PsrResponseInterface $response
     * @param ResponseInterface $serverResponse
     * @param bool $withContent
     * @return void
     */
    public function emit(PsrResponseInterface $response, ResponseInterface $serverResponse, bool $withContent = true): void;
}