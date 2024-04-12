<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client\Http;

interface RequestInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @return string
     */
    public function getBody(): string;
}