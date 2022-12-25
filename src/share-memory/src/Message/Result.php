<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Message;

class Result
{
    public function __construct(public mixed $data, public bool $success = true)
    {
    }

    public static function build(mixed $data, bool $success = true): string
    {
        $result = new static($data, $success);
        return $result->toString();
    }

    public static function parse(string $raw): Result
    {
        $data = \json_decode($raw, true);

        return new static($data['data'], $data['success']);
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'success' => $this->success,
        ];
    }

    public function toString(): string
    {
        return \json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}