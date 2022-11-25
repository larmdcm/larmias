<?php

declare(strict_types=1);

namespace Larmias\Utils\Codec;

use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Contracts\Jsonable;
use InvalidArgumentException;

class Json
{
    /**
     * @param mixed $data
     * @throws InvalidArgumentException
     */
    public static function encode($data, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        if ($data instanceof Jsonable) {
            return (string)$data;
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        try {
            $json = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0)
    {
        try {
            $decode = json_decode($json, $assoc, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}