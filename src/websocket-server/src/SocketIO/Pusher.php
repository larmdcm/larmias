<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SocketIO;

use Larmias\WebSocketServer\Pusher as BasePusher;
use Larmias\WebSocketServer\Message\Event;

class Pusher extends BasePusher
{
    /**
     * 数据消息编码
     * @param mixed $message
     * @return mixed
     */
    public function encodeMessage(mixed $message): mixed
    {
        if ($message instanceof Event) {
            $message = Packet::create(Packet::EVENT, [
                'data' => array_merge([$message->type], $message->data),
            ]);
        }

        if ($message instanceof Packet) {
            $message = EnginePacket::message($message->toString());
        }

        if ($message instanceof EnginePacket) {
            $message = $message->toString();
        }

        return $message;
    }
}