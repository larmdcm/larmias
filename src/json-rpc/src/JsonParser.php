<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Codec\Json;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Contracts\RequestInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;
use Larmias\JsonRpc\Message\Error;
use Larmias\JsonRpc\Message\Request;
use Larmias\JsonRpc\Message\Response;

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
     * @return ResponseInterface
     */
    public function decodeResponse(string $contents): ResponseInterface
    {
        $data = Json::decode($contents);
        $response = new Response(jsonrpc: $data['jsonrpc'], id: $data['id'] ?? null);

        if (isset($data['error'])) {
            $response->setError(new Error($data['error']['code'] ?? 0, $data['error']['message'] ?? '', $data['error']['data'] ?? null));
        } else {
            $response->setResult($data['result']);
        }

        return $response;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public function encodeRequest(RequestInterface $request): string
    {
        return Json::encode([
            'id' => $request->getId(),
            'jsonrpc' => $request->getJsonRpc(),
            'method' => $request->getMethod(),
            'params' => $request->getParams(),
        ]);
    }

    /**
     * @param string $contents
     * @return RequestInterface
     */
    public function decodeRequest(string $contents): RequestInterface
    {
        $data = Json::decode($contents);
        return new Request($data['method'], $data['params'], $data['jsonrpc'], $data['id'] ?? null);
    }
}