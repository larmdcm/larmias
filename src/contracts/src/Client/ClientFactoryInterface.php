<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

interface ClientFactoryInterface
{
    /**
     * @return ClientInterface
     */
    public function dialTcp(): ClientInterface;
}