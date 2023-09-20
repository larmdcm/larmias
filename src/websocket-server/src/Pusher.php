<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;
use Larmias\WebSocketServer\Contracts\PusherInterface;
use Larmias\WebSocketServer\Message\Event as EventMessage;
use function in_array;
use function array_unique;

class Pusher implements PusherInterface
{
    /**
     * @var array
     */
    protected array $to = [];

    public function __construct(protected ConnectionManagerInterface $connectionManager)
    {
    }

    /**
     * 发送给谁
     * @param ...$values
     * @return PusherInterface
     */
    public function to(...$values): PusherInterface
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                $this->to(...$value);
            } elseif (!in_array($value, $this->to)) {
                $this->to[] = $value;
            }
        }

        return $this;
    }

    /**
     * 发送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
        $ids = [];
        foreach ($this->to as $item) {
            $ids[] = $item;
        }
        $ids = array_unique($ids);

        foreach ($ids as $id) {
            $this->sendMessage($id, $data);
        }
    }

    /**
     * 推送事件消息
     * @param string $event
     * @param ...$data
     * @return void
     */
    public function emit(string $event, ...$data): void
    {
        $this->push(new EventMessage($event, $data));
    }

    /**
     * 给指定连接发送数据
     * @param int $id
     * @param mixed $data
     * @return void
     */
    public function sendMessage(int $id, mixed $data): void
    {
        $connection = $this->connectionManager->get($id);
        $connection?->send($this->encodeMessage($data));
    }

    /**
     * 数据消息编码
     * @param mixed $message
     * @return mixed
     */
    public function encodeMessage(mixed $message): mixed
    {
        return $message;
    }
}