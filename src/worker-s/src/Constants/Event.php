<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Constants;

use Larmias\WorkerS\Process\Constants\Event as WorkerEvent;

class Event extends WorkerEvent
{
    /** @var string */
    public const ON_CONNECT = 'connect';

    /** @var string */
    public const ON_RECEIVE = 'receive';

    /** @var string */
    public const ON_REQUEST = 'request';

    /** @var string */
    public const ON_OPEN = 'open';

    /** @var string */
    public const ON_MESSAGE = 'message';

    /** @var string */
    public const ON_ERROR = 'error';

    /** @var string */
    public const ON_CLOSE = 'close';
}