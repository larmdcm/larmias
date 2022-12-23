<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

class Server
{
    /**
     * @var string
     */
    public const ON_RECEIVE = 'onReceive';

    public function onReceive($conn,$data)
    {
    }
}