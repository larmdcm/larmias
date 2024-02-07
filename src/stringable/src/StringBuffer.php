<?php

declare(strict_types=1);

namespace Larmias\Stringable;

use Stringable;

class StringBuffer implements Stringable
{
    /**
     * @param string $buffer
     */
    public function __construct(protected string $buffer = '')
    {
    }

    /**
     * @param string $buffer
     * @return self
     */
    public function append(string $buffer): self
    {
        $this->buffer .= $buffer;
        return $this;
    }

    /**
     * @param string $buffer
     * @return string
     */
    public function write(string $buffer): string
    {
        return $this->buffer = $buffer;
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return string
     */
    public function take(int $offset, ?int $length = null): string
    {
        $this->buffer = substr($this->buffer, $offset, $length);
        return $this->buffer;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->buffer = '';
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->buffer === '';
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return strlen($this->buffer);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->buffer;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}