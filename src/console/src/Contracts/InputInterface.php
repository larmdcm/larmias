<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

use Larmias\Console\Input\Definition;

interface InputInterface
{
    /**
     * @param Definition $definition
     * @return void
     */
    public function setDefinition(Definition $definition): void;

    /**
     * @param string $name
     * @return string|null
     */
    public function getOption(string $name): ?string;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool;

    /**
     * @return string
     */
    public function getScriptFile(): string;

    /**
     * @return string
     */
    public function getCommand(): string;
}