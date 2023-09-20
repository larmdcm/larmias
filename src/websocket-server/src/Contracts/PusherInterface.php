<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

interface PusherInterface
{
    /**
     * 发送给谁
     * @param ...$values
     * @return PusherInterface
     */
    public function to(...$values): PusherInterface;

    /**
     * 发送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void;

    /**
     * 推送事件消息
     * @param string $event
     * @param ...$data
     * @return void
     */
    public function emit(string $event, ...$data): void;

    /**
     * 给指定连接发送数据
     * @param int $id
     * @param mixed $data
     * @return void
     */
    public function sendMessage(int $id, mixed $data): void;

    /**
     * 数据消息编码
     * @param mixed $message
     * @return mixed
     */
    public function encodeMessage(mixed $message): mixed;
}