<?php

declare(strict_types=1);

namespace Larmias\Session\Handler;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

class RedisHandler extends Driver
{
    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var array
     */
    protected array $config = [
        'prefix' => '',
        'expire' => 0,
    ];

    /**
     * @param RedisFactoryInterface $factory
     * @return void
     */
    public function initialize(RedisFactoryInterface $factory)
    {
        $this->connection = $factory->get();
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return mixed
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        $id = $this->getId($id);
        return $this->connection->del($id) > 0;
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $path the path where to store/retrieve the session
     * @param string $name the session name
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $id the session id to read data for
     * @return string
     */
    public function read(string $id): string
    {
        $id = $this->getId($id);
        return $this->connection->get($id) ?: '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $id
     * @param string $data
     */
    public function write(string $id, string $data): bool
    {
        $id = $this->getId($id);
        $expire = $this->config['expire'];
        if ($expire <= 0) {
            return (bool)$this->connection->set($id, $data);
        }
        return (bool)$this->connection->setex($id, $expire, $data);
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getId(string $id): string
    {
        return $this->config['prefix'] . $id;
    }
}