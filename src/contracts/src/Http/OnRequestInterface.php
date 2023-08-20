<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

interface OnRequestInterface
{
    /** @var string */
    public const ON_REQUEST = 'onRequest';

    /**
     * 请求回调
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function onRequest(RequestInterface $request, ResponseInterface $response): void;
}