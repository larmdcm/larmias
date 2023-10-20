<?php

declare(strict_types=1);

namespace Larmias\Engine\Concurrent;

use Larmias\Contracts\Concurrent\ParallelInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Throwable;

class Parallel implements ParallelInterface
{
    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    /**
     * @var ChannelInterface|null
     */
    protected ?ChannelInterface $concurrentChannel = null;

    /**
     * @var array
     */
    protected array $results = [];

    /**
     * @var Throwable[]
     */
    protected array $throwable = [];
}