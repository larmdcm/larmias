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
        $query = $this->newQuery()->table('t_user');

        $result = $query->where('id', 1)->where(['username' => 'test'])->where(function (QueryInterface $query) {
            $query->where('status', '=', 1)->where('integral', '>', 1)->where(function (QueryInterface $query) {
                $query->where('password', '<>', '');
            });
        })->buildSql();
        $this->assertSame($result, "SELECT * FROM `t_user` WHERE `id` = '1' AND `username` = 'test' AND ( `status` = '1' AND `integral` > '1' AND ( `password` <> '' ) )");
    }

    /**
     * @return void
     */
    public function testWhereMix(): void
    {
        $query = $this->newQuery()->table('t_user');
        $result = $query->where('id = 1')->whereNull('info')->whereNotNull('update_time')
            ->whereIn('id', [1, 2, 3])
            ->whereNotIn('id', '4,5')
            ->whereBetween('id', [1, 2])
            ->whereNotBetween('id', [4, 5])
            ->whereLike('name', '%test%')
            ->whereNotLike('name', '%test%')
            ->whereExists('id', 'test')
            ->whereNotExists('id', 'test')
            ->whereColumn('create_time', '>', 'update_time')
            ->buildSql();
        var_dump($result);
        $this->assertSame($result, "");
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $query = $this->newQuery();
        $collect = $query->table('t_user')->get();
        $this->assertTrue($collect->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testFirst(): void
    {
        $query = $this->newQuery();
        $data = $query->table('t_user')->first();
        $this->assertNotNull($data);
    }

    /**
     * @return void
     */
    public function testBuildSql(): void
    {
        $query = $this->newQuery();
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
        $query = $this->newQuery();
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
        $query = $this->newQuery();
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
        $query = $this->newQuery();
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
        $query = $this->newQuery();
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
        $query = $this->newQuery();
        $result = $query->table('t_user')->where('id', 10)->delete();
        $this->assertTrue($result > 0);
    }

    /**
     * @return QueryInterface
     */
    protected function newQuery(): QueryInterface
    {
        return $this->manager->newQuery($this->manager->connection());
    }
}