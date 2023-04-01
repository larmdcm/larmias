<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Manager;
use Larmias\Di\Container;
use PHPUnit\Framework\TestCase;
use Larmias\Database\Contracts\ConnectionInterface;
use function Larmias\Utils\println;

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
     * @return void
     * @throws \Throwable
     */
    public function testTransaction(): void
    {
        $connection = $this->getConnection();
        $ctx = $connection->beginTransaction();
        try {

            $result = $connection->execute('update t_user set integral = integral + 1 where id = ?', [1]);

            if (!$result->getRowCount()) {
                throw new \RuntimeException('修改用户信息失败');
            }

            // 测试事务嵌套
            $this->actionUpdateUser(2);
            $this->actionUpdateUser(6);

            // 突然发生异常
            throw new \RuntimeException('发生了异常');

            $ctx->commit();
        } catch (\Throwable $e) {
            $ctx->rollback();
            throw $e;
        }

        $this->assertTrue(true);
    }

    /**
     * @param int $id
     * @return void
     * @throws \Throwable
     */
    protected function actionUpdateUser(int $id): void
    {
        $connection = $this->getConnection();
        $ctx = $connection->beginTransaction();
        try {

            $result = $connection->execute('update t_user set integral = integral + 1 where id = ?', [$id]);

            if (!$result->getRowCount()) {
                throw new \RuntimeException('修改用户信息失败');
            }

            $ctx->commit();
        } catch (\Throwable $e) {
            $ctx->rollback();
            throw $e;
        }
    }


    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        return $this->manager->connection();
    }
}