<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Task\Contracts;

interface RWEventInterface
{
    /**
     * @return void
     */
    public function onReadable(): void;

    /**
     * @return void
     */
    public function onWritable(): void;

    /**
     * @return resource
     */
    public function getStream();
}
