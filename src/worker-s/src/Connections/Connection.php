<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Connections;

use Stringable;

abstract class Connection
{
    /**
     * @var int
     */
    const SEND_FAIL = 100;

    /**
     * @var array
     */
    public static array $statistics = [
        'connection_count' => 0,
        'total_request'    => 0,
        'total_response'   => 0,
        'throw_exception'  => 0,
        'send_fail'        => 0,
    ];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * Connection __set

     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * Connection __set
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name,$value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Connection __isset
     *
     * @param  string  $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * @param string $data
     * @param boolean $isRaw
     * @return integer
     */
    abstract public function send(string|Stringable $data = '',bool $isRaw = false): int;

    /**
     * @param string|Stringable $data
     * @param boolean $isRaw
     * @return void
     */
    abstract public function close(string|Stringable $data = null,bool $isRaw = false): void;
}
