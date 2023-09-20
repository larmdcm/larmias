<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Room;

use Larmias\WebSocketServer\Contracts\RoomInterface;
use function is_string;
use function explode;
use function in_array;

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
     * @return RoomInterface
     */
    public function join(int $id, array|string $rooms): RoomInterface
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
        return $this;
    }
}