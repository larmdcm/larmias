<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

interface RoomInterface
{
    /**
     * 加入房间.
     * @param int $id
     * @param array|string $rooms
     * @return void
     */
    public function join(int $id, array|string $rooms): void;

    /**
     * 离开房间.
     * @param int $id
     * @param array|string $rooms
     * @return void
     */
    public function leave(int $id, array|string $rooms): void;

    /**
     * 获取房间客户列表
     * @param string $room
     * @return array
     */
    public function getClients(string $room): array;
}