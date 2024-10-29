<?php

declare(strict_types=1);

namespace Larmias\Codec;

use InvalidArgumentException;
use Larmias\Contracts\Arrayable;
use Larmias\Contracts\Jsonable;
use Throwable;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class Json
{
    /**
     * JSON编码
     * @param mixed $data
     * @param int $flags
     * @param int $depth
     * @return string
     * @throws InvalidArgumentException
     */
    public static function encode(mixed $data, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        if ($data instanceof Jsonable) {
            return $data->toJson($flags);
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        try {
            $json = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $json;
    }

    /**
     * JSON解码
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $flags
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0): mixed
    {
        try {
            $decode = json_decode($json, $assoc, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}