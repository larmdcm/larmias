<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Contracts;

use JsonSerializable;

interface RequestInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     */
    public function setMethod(string $method): void;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param array $params
     */
    public function setParams(array $params): void;

    /**
     * @return string
     */
    public function getJsonRpc(): string;

    /**
     * @param string $jsonrpc
     */
    public function setJsonRpc(string $jsonrpc): void;

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void;
}