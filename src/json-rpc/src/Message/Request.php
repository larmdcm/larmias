<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Message;

use Larmias\JsonRpc\Contracts\RequestInterface;

class Request implements RequestInterface
{
    public function __construct(
        protected string  $method,
        protected array   $params = [],
        protected string  $jsonrpc = '2.0',
        protected ?string $id = null,
    )
    {
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getJsonRpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @param string $jsonrpc
     */
    public function setJsonRpc(string $jsonrpc): void
    {
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}