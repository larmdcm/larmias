<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Manager;
use PHPUnit\Framework\TestCase;
use Larmias\Database\Contracts\ConnectionInterface;

class ConnectionTest extends TestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testQuery(): void
    {
        $connection = $this->getConnection();
        $connection->query("select * from page");
        $this->assertTrue(true);
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        $connection = new MysqlConnection([
            'type' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => '',
            'password' => '',
            'database' => '',
            'charset' => 'utf8mb4',
            'dsn' => '',
            'socket' => '',
            'options' => [],
            'prefix' => '',
        ]);

        $connection->connect();

        return $connection;
    }
}