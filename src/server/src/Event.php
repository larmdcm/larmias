<?php

declare(strict_types=1);

namespace Larmias\Server;

class Event
{
    /**
     * @var string
     */
    const ON_WORKER_START = 'workerStart';

    /**
     * @var string
     */
    const ON_WORKER_STOP = 'workerStop';

    /**
     * @var string
     */
    const ON_CONNECT = 'connect';

    /**
     * @var string
     */
    const ON_CLOSE = 'close';

    /**
     * @var string
     */
    const ON_REQUEST = 'request';
}