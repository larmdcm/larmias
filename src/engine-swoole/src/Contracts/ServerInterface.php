<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Contracts;

interface ServerInterface
{
    public function initServer(): void;
}