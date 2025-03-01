<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\WebSocket;

use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Engine\Swoole\Http\Request;
use Larmias\Engine\Swoole\Http\Response;

class Connection implements ConnectionInterface
{
    /**
     * @param int $id
     * @param Request $request
     * @param Response $response
     */
    public function __construct(protected int $id, protected Request $request, protected Response $response)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->response->getSwooleResponse()->fd;
    }

    /**
     * @return mixed
     */
    public function recv(): mixed
    {
        return $this->response->getSwooleResponse()->recv();
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed
    {
        return $this->response->getSwooleResponse()->push($data);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->response->getSwooleResponse()->close();
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getRawConnection(): Response
    {
        return $this->response;
    }
}