<?php

declare(strict_types=1);

namespace Larmias\Engine;

class Event
{
    /** @var string */
    public const ON_WORKER_START = 'workerStart';

    /** @var string */
    public const ON_WORKER = 'worker';

    /** @var string */
    public const ON_WORKER_SIGNAL = 'workerSignal';

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
    public const ON_HAND_SHAKE = 'handShake';

    /** @var string */
    public const ON_OPEN = 'open';

    /** @var string */
    public const ON_MESSAGE = 'message';

    /** @var string */
    public const ON_CLOSE = 'close';
}