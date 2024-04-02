<?php

declare(strict_types=1);

namespace Larmias\Codec\Protocol;

use Larmias\Contracts\ProtocolInterface;

class TextProtocol implements ProtocolInterface
{
    /**
     * @var string
     */
    protected string $eof = "\n";

    /**
     * @param mixed $data
     * @return int
     */
    public function input(mixed $data): int
    {
        $pos = strpos($data, $this->eof);
        if ($pos === false) {
            return 0;
        }
        return $pos + 1;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return $data . $this->eof;
    }

    /**
     * @param string $data
     * @return string
     */
    public function unpack(string $data): string
    {
        return rtrim($data, "\n");
    }

    /**
     * @return string
     */
    public function getEof(): string
    {
        return $this->eof;
    }

    /**
     * @param string $eof
     */
    public function setEof(string $eof): void
    {
        $this->eof = $eof;
    }
}