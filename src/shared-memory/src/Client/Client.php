<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

class Client
{
    protected ?Connection $connection = null;

    public function __construct(protected ClientFactory $clientFactory)
    {
    }

    /**
     * @return Connection
     * @throws \Throwable
     */
    public function getConnection(): Connection
    {
        if (!$this->connection) {
            $this->connection = $this->clientFactory->make();
        }

        return $this->connection;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->getConnection()->{$name}(...$arguments);
    }
}