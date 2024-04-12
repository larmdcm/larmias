<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client\Http;

interface ClientInterface
{
    /**
     * @param array $options
     * @return ClientInterface
     */
    public function setOptions(array $options): ClientInterface;

    /**
     * @param string $method
     * @param string $path
     * @param string $contents
     * @param array $headers
     * @param string $version
     * @return ResponseInterface
     */
    public function request(string $method, string $path = '/', string $contents = '', array $headers = [], string $version = '1.1'): ResponseInterface;
}