<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Contracts;

use JsonSerializable;

interface ResponseInterface extends JsonSerializable
{
    /**
     * @return mixed
     */
    public function getResult(): mixed;

    /**
     * @param mixed $result
     */
    public function setResult(mixed $result): void;

    /**
     * @return ErrorInterface|null
     */
    public function getError(): ?ErrorInterface;

    /**
     * @param ErrorInterface|null $error
     */
    public function setError(?ErrorInterface $error): void;

    /**
     * @return string
     */
    public function getJsonrpc(): string;

    /**
     * @param string $jsonrpc
     */
    public function setJsonrpc(string $jsonrpc): void;

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void;
}