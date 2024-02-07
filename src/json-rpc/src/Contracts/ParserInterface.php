<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Contracts;

interface ParserInterface
{
    /**
     * @param ResponseInterface $response
     * @return string
     */
    public function encodeResponse(ResponseInterface $response): string;

    /**
     * @param string $contents
     * @return RequestInterface
     */
    public function decodeRequest(string $contents): RequestInterface;
}