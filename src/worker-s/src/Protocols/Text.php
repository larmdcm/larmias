<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols;

use Larmias\WorkerS\Connections\Connection;

class Text implements ProtocolInterface
{
    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return int
     */
    public static function input(string $data,?Connection $connection): int
    {
        $pos = \strpos($data,"\n");
        if ($pos === false) {
            return 0;
        }
        return $pos + 1;
    }

    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return string
     */
    public static function encode(string $data,?Connection $connection): string
    {
        return $data . "\n";
    }

    /**
     * @param string     $data
     * @param Connection|null $connection
     * @return string
     */
    public static function decode(string $data,?Connection $connection): string
    {
        return \rtrim($data,PHP_EOL);
    }
}