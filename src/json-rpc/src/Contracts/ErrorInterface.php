<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Contracts;

interface ErrorInterface
{
    /**
     * @return int
     */
    public function getCode(): int;

    /**
     * @param int $code
     */
    public function setCode(int $code): void;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     */
    public function setMessage(string $message): void;

    /**
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * @param mixed $data
     */
    public function setData(mixed $data): void;
}