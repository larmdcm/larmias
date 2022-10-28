<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ConfigInterface
{
    /**
     * 加载文件配置
     *
     * @param string      $file
     * @param string|null $key
     * @return array
     */
    public function load(string $file,?string $key = null): array;

    /**
     * 获取配置是否存在
     *
     * @param  string $key
     * @return boolean
     */
    public function has(string $key): bool;

    /**
     * 设置配置参数.
     *
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    public function set(string $key,mixed $value): array;

    /**
     * 获取配置参数
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null,mixed $default = null): mixed;
    /**
     * 数组配置追加
     *
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    public function push(string $key,mixed $value): array;

    /**
     * 获取全部配置
     *
     * @return array
     */
    public function all(): array;
}
