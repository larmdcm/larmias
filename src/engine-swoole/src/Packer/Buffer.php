<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Packer;

class Buffer
{
    /**
     * @var string
     */
    protected string $data = '';

    /**
     * @param string $data
     * @return void
     */
    public function append(string $data): void
    {
        $this->data .= $data;
    }

    /**
     * @param string $data
     * @return void
     */
    public function write(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->data = '';
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->data;
    }
}