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
    public const VERSION = '2.0';

    /**
     * @param ResponseInterface $response
     * @return string
     */
    public function encodeResponse(ResponseInterface $response): string
    {
        $error = $response->getError();
        $data = [
            'id' => $response->getId(),
            'jsonrpc' => self::VERSION,
        ];
        if ($error) {
            $data['error'] = $error;
        } else {
            $data['result'] = $response->getResult();
        }
        return Json::encode($data);
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