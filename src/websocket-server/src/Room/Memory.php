<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Room;

use Larmias\WebSocketServer\Contracts\RoomInterface;
use function is_string;
use function explode;
use function in_array;
use function array_values;
use function array_diff;

class Memory implements RoomInterface
{
    /**
     * @var array
     */
    protected array $rooms = [];

    /**
     * 加入房间.
     * @param int $id
     * @param array|string $rooms
     * @return void
     */
    public function join(int $id, array|string $rooms): void
    {
        $rooms = is_string($rooms) ? explode(',', $rooms) : $rooms;
        foreach ($rooms as $room) {
            if (!isset($this->rooms[$room])) {
                $this->rooms[$room] = [];
            }

            if (in_array($id, $this->rooms[$room])) {
                continue;
            }

            $this->rooms[$room][] = $id;
        }
    }

    /**
     * 离开房间.
     * @param int $id
     * @param array|string $rooms
     * @return void
     */
    public function leave(int $id, array|string $rooms): void
    {
        $rooms = is_string($rooms) ? explode(',', $rooms) : $rooms;
        foreach ($rooms as $room) {
            if (!in_array($id, $this->rooms[$room])) {
                continue;
            }

            $this->rooms[$room][] = array_values(array_diff($this->rooms[$room], [$id]));
        }
    }

    /**
     * 获取房间客户列表
     * @param string $room
     * @return array
     */
    public function getClients(string $room): array
    {
        return $this->rooms[$room] ?? [];
    }
}