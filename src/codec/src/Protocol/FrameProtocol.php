<?php

declare(strict_types=1);

namespace Larmias\Codec\Protocol;

use Larmias\Contracts\ProtocolInterface;
use RuntimeException;

class FrameProtocol implements ProtocolInterface
{
    /** @var int */
    public const HEADER_SIZE = 4;

    /** @var string */
    public const HEADER_STRUCT = 'Nlength';

    /** @var string */
    public const HEADER_PACK = 'N';

    /**
     * @param mixed $data
     * @return int
     */
    public function input(mixed $data): int
    {
        $totalLen = strlen($data);
        if ($totalLen < self::HEADER_SIZE) {
            return 0;
        }

        $header = unpack(self::HEADER_STRUCT, $data);
        if ($header === false) {
            throw new RuntimeException('Invalid Header');
        }

        return (int)$header['length'];
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return pack(self::HEADER_PACK, self::HEADER_SIZE + strlen($data)) . $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public function unpack(string $data): string
    {
        return substr($data, self::HEADER_SIZE);
    }
}