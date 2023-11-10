<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

class ConnectionTest extends TestCase
{
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
        $connection->beginTransaction();
        try {

            $result = $connection->execute('update t_user set integral = integral + 1,update_time = now() where id = ?', [1]);

            if (!$result->getRowCount()) {
                throw new \RuntimeException('修改用户信息失败');
            }

            // 测试事务嵌套
            $this->actionUpdateUser(2);
            $this->actionUpdateUser(3);

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollback();
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
        $connection->beginTransaction();
        try {

            $result = $connection->execute('update t_user set integral = integral + 1,update_time = now() where id = ?', [$id]);

            if (!$result->getRowCount()) {
                throw new \RuntimeException('修改用户信息失败');
            }

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollback();
            throw $e;
        }
    }
}