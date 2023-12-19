<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

interface RoomInterface
{
    /**
     * 加入房间.
     * @param string $id
     * @param array|string $rooms
     * @return void
     */
    public function join(string $id, array|string $rooms): void;

    /**
     * 离开房间.
     * @param string $id
     * @param array|string $rooms
     * @return void
     */
    public function leave(string $id, array|string $rooms): void;

    /**
     * 获取房间客户列表
     * @param string $room
     * @return array
     */
    public function getClients(string $room): array;
}