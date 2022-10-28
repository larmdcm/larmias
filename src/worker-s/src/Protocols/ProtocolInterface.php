<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols;

use Larmias\WorkerS\Connections\Connection;

interface ProtocolInterface
{
    /**
     * @param string          $data
     * @param Connection|null $connection
     * @return int
     */
    public static function input(string $data,?Connection $connection): int;

    /**
     * @param string          $data
     * @param Connection|null $connection
     * @return mixed
     */
    public static function encode(string $data,?Connection $connection): mixed;

    /**
     * @param string          $data
     * @param Connection|null $connection
     * @return mixed
     */
    public static function decode(string $data,?Connection $connection): mixed;
}