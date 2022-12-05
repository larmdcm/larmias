<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

use Larmias\Console\Input\Definition;

interface InputInterface
{
    /**
     * @param Definition $definition
     * @return InputInterface
     */
    public function bind(Definition $definition): InputInterface;

    /**
     * @param string $name
     * @return mixed
     */
    public function getArgument(string $name): mixed;

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool;

    /**
     * @return array
     */
    public function getArguments(): array;

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption(string $name): mixed;

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

    /**
     * 获取输入的参数
     *
     * @param string|int $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getInputParam(string|int $key, mixed $default = null): mixed;

    /**
     * 输入的参数是否存在
     *
     * @param string|int $key
     * @return bool
     */
    public function hasInputParam(string|int $key): bool;
}