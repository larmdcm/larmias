<?php

declare(strict_types=1);

namespace Larmias\Contracts\Concurrent;

use RuntimeException;

class ParallelExecutionException extends RuntimeException
{
    protected array $results = [];

    protected array $throwable = [];

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;
    }

    public function getThrowable(): array
    {
        return $this->throwable;
    }

    public function setThrowables(array $throwable)
    {
        return $this->throwable = $throwable;
    }
}