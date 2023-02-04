<?php

declare(strict_types=1);

namespace Larmias\Task;

class Task
{
    /**
     * @var int
     */
    protected int $priority = 1;

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }
}