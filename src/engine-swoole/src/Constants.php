<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

class Constants
{
    /**
     * @var int
     */
    public const STATUS_NORMAL = 0;

    /**
     * @var int
     */
    public const STATUS_STOP = 101;

    /**
     * @var int
     */
    public const STATUS_RELOAD = 102;

    /**
     * @var int
     */
    public const EXIT_CODE_NORMAL = 0;

    /**
     * @var int
     */
    public const EXIT_CODE_FAIL = 103;
}