<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Client\Proxy\TcpClient;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Contracts\RequestInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;
use Larmias\JsonRpc\Exceptions\JsonRpcResponseException;
use Larmias\JsonRpc\Message\Request;
use Larmias\JsonRpc\Protocol\FrameProtocol;

class Client
{
    /**
     * @var TcpClient
     */
    protected TcpClient $client;

    /**
     * @var ParserInterface
     */
    protected ParserInterface $parser;

    /**
     * @param ContainerInterface $container
     * @throws \Throwable
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->parser = $this->container->get(ParserInterface::class);

        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);

        $options = [
            'protocol' => FrameProtocol::class,
        ];

        $this->client = new TcpClient($this->container, array_merge($options, $config->get('jsonRpc', [])));
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function callRequest(RequestInterface $request): ResponseInterface
    {
        $data = $this->parser->encodeRequest($request);
        $result = $this->client->sendAndRecv($data);
        if (!$result) {
            throw new JsonRpcResponseException('Service call failed');
        }

        return $this->parser->decodeResponse($result);
    }

    /**
     * @param string $method
     * @param ...$args
     * @return mixed
     */
    public function call(string $method, ...$args): mixed
    {
        $response = $this->callRequest(new Request($method, $args));

        if ($response->getError()) {
            throw new JsonRpcResponseException($response->getError()->getMessage(), $response->getError()->getCode());
        }

        return $response->getResult();
    }
}