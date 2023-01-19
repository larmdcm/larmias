<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ViewInterface
{
    /**
     * 分配视图变量
     *
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(string|array $name, mixed $value = null): ViewInterface;

    /**
     * 渲染视图
     *
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string;
}