<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Message;

use Stringable;

class ServerSentEvents implements Stringable
{
    /**
     * @param array $data
     */
    public function __construct(protected array $data = [])
    {
    }

    /**
     * @param array $data
     * @return ServerSentEvents
     */
    public static function make(array $data = []): ServerSentEvents
    {
        return new static($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $buffer = '';
        $data = $this->data;
        if (isset($data['comment'])) {
            $buffer = ": {$data['comment']}\n";
        }
        if (isset($data['event'])) {
            $buffer .= "event: {$data['event']}\n";
        }
        if (isset($data['id'])) {
            $buffer .= "id: {$data['id']}\n";
        }
        if (isset($data['retry'])) {
            $buffer .= "retry: {$data['retry']}\n";
        }
        if (isset($data['data'])) {
            $buffer .= 'data: ' . str_replace("\n", "\ndata: ", $data['data']) . "\n";
        }
        return $buffer . "\n";
    }
}