<?php

declare(strict_types=1);

namespace Larmias\Contracts\Worker;

interface WorkerInterface
{
    /**
     * @return int
     */
    public function getWorkerId(): int;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getSettings(string $name, mixed $default = null): mixed;
}