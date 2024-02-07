<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Codec\Json;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Contracts\RequestInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;
use Larmias\JsonRpc\Message\Request;

class JsonParser implements ParserInterface
{
    /**
     * @param ResponseInterface $response
     * @return string
     */
    public function encodeResponse(ResponseInterface $response): string
    {
        return Json::encode([

        ]);
    }

    /**
     * @param string $contents
     * @return RequestInterface
     */
    public function decodeRequest(string $contents): RequestInterface
    {
        $data = Json::decode($contents);
        return new Request($data['method'], $data['params'], $data['context'] ?? [], $data['jsonrpc'], $data['id'] ?? null);
    }
}