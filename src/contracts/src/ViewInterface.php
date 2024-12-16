<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ViewInterface
{
    /**
     * 添加搜索路径
     * @param string $location
     * @return void
     */
    public function addLocation(string $location): void;

    /**
     * 添加命名空间查找路径
     * @param string $namespace
     * @param string|array $hints
     * @return void
     */
    public function addNamespace(string $namespace, string|array $hints): void;

    /**
     * 分配视图变量
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(string|array $name, mixed $value = null): ViewInterface;
    
    /**
     * 渲染视图
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string;
}