<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\JobInterface;
use Serializable;

abstract class Job implements JobInterface, Serializable
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return JobInterface
     */
    public function setData(array $data): JobInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([
            'data' => $this->data,
        ]);
    }

    /**
     * @param string $data
     * @return void
     */
    public function unserialize(string $data): void
    {
        $object = \unserialize($data);
        $this->data = $object['data'];
    }
}