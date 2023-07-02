<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseEmitterInterface
{
    /**
     * @param PsrResponseInterface $response
     * @param ResponseInterface $rawResponse
     * @param bool $withContent
     * @return void
     */
    public function emit(PsrResponseInterface $response, ResponseInterface $rawResponse, bool $withContent = true): void;
}