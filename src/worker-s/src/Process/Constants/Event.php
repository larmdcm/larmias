<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Constants;

class Event
{
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

}