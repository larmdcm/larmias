<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

interface OnRequestInterface
{
    /** @var string */
    public const ON_REQUEST = 'onRequest';

    /**
     * @param RequestInterface $serverRequest
     * @param ResponseInterface $serverResponse
     * @return void
     */
    public function onRequest(RequestInterface $serverRequest, ResponseInterface $serverResponse): void;
}