<?php

declare(strict_types=1);

namespace Larmias\Engine;

class Event
{
    /** @var string */
    public const ON_BEFORE_START = 'beforeStart';

    /** @var string */
    public const ON_MASTER_START = 'masterStart';

    /** @var string */
    public const ON_MASTER = 'master';

    /** @var string */
    public const ON_MASTER_SIGNAL = 'masterSignal';

    /** @var string */
    public const ON_MASTER_CUSTOM = 'masterCustom';

    /** @var string */
    public const ON_MASTER_STOP = 'masterStop';

    /** @var string */
    public const ON_WORKER_START = 'workerStart';

    /** @var string */
    public const ON_WORKER = 'worker';

    /** @var string */
    public const ON_WORKER_SIGNAL = 'workerSignal';

    /** @var string */
    public const ON_WORKER_CUSTOM = 'workerCustom';

    /** @var string */
    public const ON_WORKER_STOP = 'workerStop';

    /** @var string */
    public const ON_CONNECT = 'connect';

    /** @var string */
    public const ON_RECEIVE = 'receive';

    /** @var string */
    public const ON_PACKET = 'packet';

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