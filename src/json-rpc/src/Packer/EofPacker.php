<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Packer;

use Larmias\Contracts\PackerInterface;

class EofPacker implements PackerInterface
{
    public function __construct(protected string $eof = "\r\n")
    {
    }

    public function pack(mixed $data): string
    {
        return $data . $this->eof;
    }

    public function unpack(string $data): string
    {
        return rtrim($data, $this->eof);
    }
}