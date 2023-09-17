<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\WebSocketServer\Contracts\PusherInterface;

class Pusher implements PusherInterface
{
    /**
     * @var array
     */
    protected array $to = [];

    /**
     * 发送给谁
     * @param ...$values
     * @return self
     */
    public function to(...$values): self
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                $this->to(...$value);
            } elseif (!in_array($value, $this->to)) {
                $this->to[] = $value;
            }
        }

        return $this;
    }

    /**
     * 发送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
    }
}