<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

abstract class Command
{
    public function __construct(protected array $args = [])
    {
    }

    public function handle(): mixed
    {
        return null;
    }

    public function call(): mixed
    {
        $method = 'handle';
        if (\count($this->args) > 0) {
            $method = array_shift($this->args);
        }
        return \call_user_func_array([$this, $method], $this->args);
    }
}