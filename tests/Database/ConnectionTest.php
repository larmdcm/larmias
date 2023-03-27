<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Manager;
use Larmias\Di\Container;
use PHPUnit\Framework\TestCase;
use Larmias\Database\Contracts\ConnectionInterface;

class ConnectionTest extends TestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager(Container::getInstance(), require __DIR__ . '/database.php');
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testQuery(): void
    {
        $connection = $this->getConnection();
        $list = $connection->query("select * from t_user where id = ?", [1])->getResultSet();
        $this->assertNotEmpty($list);
        $this->assertSame($list[0]['id'], 1);
    }


    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        return $this->manager->connection();
    }
}