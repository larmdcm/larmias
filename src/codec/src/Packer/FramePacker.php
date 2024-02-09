<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Contracts\PackerInterface;
use RuntimeException;
use function pack;
use function substr;
use function strlen;
use function unpack;

class FramePacker implements PackerInterface
{
    /** @var int */
    public const HEADER_SIZE = 4;

    /** @var string */
    public const HEADER_STRUCT = 'Nlength';

    /** @var string */
    public const HEADER_PACK = 'N';

    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return pack(self::HEADER_PACK, strlen($data) + self::HEADER_SIZE) . $data;
    }

    /**
     * @param string $data
     * @return string[]
     */
    public function unpack(string $data): array
    {
        $totalLen = strlen($data);

        if ($totalLen < self::HEADER_SIZE) {
            return [];
        }
        $header = unpack(self::HEADER_STRUCT, $data);
        if ($header === false) {
            throw new RuntimeException('Invalid Header');
        }

        if ($totalLen < $header['length']) {
            return [];
        }

        return [substr($data, self::HEADER_SIZE, $header['length'] - self::HEADER_SIZE), substr($data, $header['length'])];
    }
}