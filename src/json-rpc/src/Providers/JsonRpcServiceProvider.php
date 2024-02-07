<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Providers;

use Larmias\Framework\ServiceProvider;

class JsonRpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->bindIf([

        ]);
    }
}