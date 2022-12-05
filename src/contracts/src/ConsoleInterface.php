<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ConsoleInterface
{
    /**
     * @param string $commandClass
     * @param string $name
     * @return \Larmias\Contracts\ConsoleInterface
     */
    public function addCommand(string $commandClass, string $name = ''): ConsoleInterface;

    /**
     * @return int
     */
    public function run(): int;
}