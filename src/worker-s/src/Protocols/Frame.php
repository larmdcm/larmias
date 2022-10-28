<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols;

use Larmias\WorkerS\Connections\Connection;

class Frame implements ProtocolInterface
{
    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return int
     */
    public static function input(string $data,?Connection $connection): int
    {
        if (\strlen($data) < 4) {
            return 0;
        }
        $unpack = \unpack('Ntotal',$data);
        return $unpack['total'];
    }

    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return string
     */
    public static function encode(string $data,?Connection $connection): string
    {
        $total = \strlen($data) + 4;
        return \pack('N', $total) . $data;
    }

    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return string
     */
    public static function decode(string $data,?Connection $connection): string
    {
        return \substr($data, 4);
    }
}