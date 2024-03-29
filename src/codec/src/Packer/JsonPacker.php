<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Codec\Json;
use Larmias\Contracts\PackerInterface;

class JsonPacker implements PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return Json::encode($data);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(string $data): mixed
    {
        return Json::decode($data);
    }
}