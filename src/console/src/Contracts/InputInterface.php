<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

interface InputInterface
{
    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @return string
     */
    public function getCommand(): string;
}