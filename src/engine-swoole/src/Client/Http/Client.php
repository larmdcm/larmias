<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Client\Http;

use Larmias\Contracts\Client\Http\ClientInterface;
use Larmias\Contracts\Client\Http\ResponseInterface;
use Larmias\Contracts\Client\Http\ClientException;
use Swoole\Coroutine\Http\Client as HttpClient;

class Client extends HttpClient implements ClientInterface
{
    /**
     * @param array $options
     * @return ClientInterface
     */
    public function setOptions(array $options): ClientInterface
    {
        $this->set($options);
        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $contents
     * @param array $headers
     * @param string $version
     * @return ResponseInterface
     */
    public function request(string $method, string $path = '/', string $contents = '', array $headers = [], string $version = '1.1'): ResponseInterface
    {
        $this->setMethod($method);
        $this->setData($contents);
        $this->setHeaders($this->encodeHeaders($headers));
        $this->execute($path);
        if ($this->errCode !== 0) {
            throw new ClientException($this->errMsg, $this->errCode);
        }
        return new Response(
            $this->statusCode,
            $this->decodeHeaders($this->headers ?? []),
            $this->body,
            $version
        );
    }

    /**
     * @param string[] $headers
     * @return string[][]
     */
    private function decodeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $name => $header) {
            // The key of header is lower case.
            $result[$name][] = $header;
        }
        if ($this->set_cookie_headers) {
            $result['set-cookie'] = $this->set_cookie_headers;
        }
        return $result;
    }

    /**
     * Swoole engine not support two-dimensional array.
     * @param string[][] $headers
     * @return string[]
     */
    private function encodeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $name => $value) {
            $result[$name] = is_array($value) ? implode(',', $value) : $value;
        }

        return $result;
    }
}