<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseEmitterInterface
{
    /**
     * @param PsrResponseInterface $psrResponse
     * @param ResponseInterface $response
     * @param bool $withContent
     * @return void
     */
    public function emit(PsrResponseInterface $psrResponse, ResponseInterface $response, bool $withContent = true): void;
}