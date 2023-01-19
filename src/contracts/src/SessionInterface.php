<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface SessionInterface
{
    /**
     * @return bool
     */
    public function start(): bool;

    /**
     * @return bool
     */
    public function save(): bool;

    /**
     * 获取全部session数据
     *
     * @return array
     */
    public function all(): array;

    /**
     * 设置session数据
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, mixed $value): bool;

    /**
     * 获取session数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * 获取session并删除
     *
     * @param string $name
     * @return mixed
     */
    public function pull(string $name): mixed;

    /**
     * 添加数据到一个session数组
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function push(string $key, mixed $value): bool;

    /**
     * 判断session数据是否存在
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * 删除session数据
     *
     * @param string $name
     * @return bool
     */
    public function delete(string $name): bool;

    /**
     * 清空session数据
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * 销毁session
     *
     * @return void
     */
    public function destroy(): void;

    /**
     * @param string|null $id
     * @return bool
     */
    public function validId(?string $id = null): bool;

    /**
     * @return string
     */
    public function generateSessionId(): string;

    /**
     * 重新生成session id
     *
     * @param bool $destroy
     * @return void
     */
    public function regenerate(bool $destroy = false): void;

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @param array $data
     */
    public function setData(array $data): void;

    /**
     * 设置sessionId
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * 设置SessionName
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * 获取sessionName
     * @return string
     */
    public function getName(): string;

    /**
     * 获取sessionId
     *
     * @return string
     */
    public function getId(): string;
}