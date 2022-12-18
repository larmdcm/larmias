<?php

namespace Larmias\Contracts\Http;

interface OnRequestInterface
{
    /**
     * @param RequestInterface $serverRequest
     * @param ResponseInterface $serverResponse
     * @return void
     */
    public function onRequest(RequestInterface $serverRequest, ResponseInterface $serverResponse): void;
}