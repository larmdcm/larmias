<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Manager;
use PHPUnit\Framework\TestCase;
use Larmias\Database\Contracts\QueryInterface;

class QueryTest extends TestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager(container(), require __DIR__ . '/database.php');
    }

    /**
     * @return void
     */
    public function testWhereClosure(): void
    {
        $query = $this->getQuery()->table('t_user');

        $result = $query->where('id', 1)->where(['username' => 'test'])->where(function (QueryInterface $query) {
            $query->where('status', '=', 1)->where('integral', '>', 1)->where(function (QueryInterface $query) {
                $query->where('password', '<>', '');
            });
        })->buildSql();
        var_dump($result);
        $this->assertSame($result, '');
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $query = $this->getQuery();
        $collect = $query->table('t_user')->get();
        $this->assertTrue($collect->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testFirst(): void
    {
        $query = $this->getQuery();
        $data = $query->table('t_user')->first();
        $this->assertNotNull($data);
    }

    /**
     * @return void
     */
    public function testBuildSql(): void
    {
        $query = $this->getQuery();
        $query->table('test')->field('id,name')->where('id', 1);
        $this->assertSame($query->buildSql(), 'SELECT`id`,`name` FROM `test` WHERE `id` = \'1\'');
        $query->where('integral', '>', 100);
        $this->assertSame($query->buildSql(), 'SELECT`id`,`name` FROM `test` WHERE `id` = \'1\' AND `integral` > \'100\'');
    }

    /**
     * @return void
     */
    public function testInsert(): void
    {
        $query = $this->getQuery();
        $result = $query->table('t_user')->insert([
            'username' => 'test',
            'password' => md5('123456')
        ]);
        $this->assertTrue($result > 0);
    }

    /**
     * @return void
     */
    public function testInsertAll(): void
    {
        $query = $this->getQuery();
        $result = $query->table('t_user')->insertAll([
            [
                'username' => 'test1',
                'password' => md5('123456')
            ],
            [
                'username' => 'test2',
                'password' => md5('123456')
            ]
        ]);
        $this->assertTrue($result > 0);
    }

    /**
     * @return void
     */
    public function testInsertGetId(): void
    {
        $query = $this->getQuery();
        $data = $query->table('t_user')->orderBy('id', 'DESC')->first();
        $id = $data ? $data['id'] : 0;
        $result = $query->table('t_user')->insertGetId([
            'username' => 'test',
            'password' => md5('123456')
        ]);
        $this->assertSame(intval($result), $id + 1);
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $query = $this->getQuery();
        $result = $query->table('t_user')->where('id', 1)->update([
            'password' => md5('123456789'),
            'update_time' => date('Y-m-d H:i:s')
        ]);
        $this->assertTrue($result > 0);
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $query = $this->getQuery();
        $result = $query->table('t_user')->where('id', 10)->delete();
        $this->assertTrue($result > 0);
    }

    /**
     * @return QueryInterface
     */
    protected function getQuery(): QueryInterface
    {
        return $this->manager->query();
    }
}