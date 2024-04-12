<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client\Http;

interface ClientFactoryInterface
{
    /**
     * @param string $url
     * @return ClientInterface
     */
    public function make(string $url): ClientInterface;
}