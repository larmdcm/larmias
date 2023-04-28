<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Store;

use Larmias\SharedMemory\Contracts\StrInterface;
use function array_key_exists;
use function count;

class Str implements StrInterface
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
    
    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        $this->data[$key] = $value;
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        unset($this->data[$key]);
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param string $key
     * @param int $step
     * @return string
     */
    public function incr(string $key, int $step): string
    {
        if ($this->exists($key)) {
            $value = (int)$this->get($key);
            $value += $step;
        } else {
            $value = (string)$step;
            $this->set($key, $value);
        }
        return (string)$value;
    }

    /**
     * @param string $key
     * @param int $step
     * @return string
     */
    public function decr(string $key, int $step): string
    {
        return $this->incr($key, -$step);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $this->data = [];
        return true;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }
}