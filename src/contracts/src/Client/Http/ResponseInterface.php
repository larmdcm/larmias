<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client\Http;

interface ResponseInterface
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * @return string
     */
    public function getVersion(): string;
}