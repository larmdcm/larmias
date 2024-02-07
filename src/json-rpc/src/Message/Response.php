<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Message;

use Larmias\JsonRpc\Contracts\ErrorInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;

class Response implements ResponseInterface
{
    public function __construct(
        protected mixed           $result = [],
        protected ?ErrorInterface $error = null,
        protected string          $jsonrpc = '2.0',
        protected ?string         $id = null,
    )
    {
    }

    /**
     * @return mixed
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }

    /**
     * @return ErrorInterface|null
     */
    public function getError(): ?ErrorInterface
    {
        return $this->error;
    }

    /**
     * @param ErrorInterface|null $error
     */
    public function setError(?ErrorInterface $error): void
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @param string $jsonrpc
     */
    public function setJsonrpc(string $jsonrpc): void
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