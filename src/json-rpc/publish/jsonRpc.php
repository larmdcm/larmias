<?php

declare(strict_types=1);

use Larmias\JsonRpc\Protocol\FrameProtocol;

return [
    'host' => '127.0.0.1',
    'port' => 2000,
    'timeout' => 3,
    'protocol' => FrameProtocol::class,
];