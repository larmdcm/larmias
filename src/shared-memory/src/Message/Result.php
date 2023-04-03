<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Message;

use Stringable;
use function json_decode;
use function json_encode;

class Result implements Stringable
{
    /**
     * @param mixed $data
     * @param bool $success
     */
    public function __construct(public mixed $data, public bool $success = true)
    {
    }

    /**
     * @param mixed $data
     * @param bool $success
     * @return string
     */
    public static function build(mixed $data, bool $success = true): string
    {
        $result = new static($data, $success);
        return $result->toString();
    }

    /**
     * @param string $raw
     * @return Result
     */
    public static function parse(string $raw): Result
    {
        $data = json_decode($raw, true);

        return new static($data['data'], $data['success']);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'success' => $this->success,
        ];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}