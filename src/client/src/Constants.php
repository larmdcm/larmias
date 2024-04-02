<?php

declare(strict_types=1);

namespace Larmias\Client;

class Constants
{
    /**
     * @var string
     */
    public const EVENT_CONNECT = 'connect';

    /**
     * @var string
     */
    public const EVENT_RECONNECT = 'reconnect';

    /**
     * @var string
     */
    public const EVENT_CLOSE = 'close';
}