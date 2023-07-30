<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Contracts\QueryInterface;

class QueryTest extends TestCase
{
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
        $this->assertSame($result, "SELECT * FROM `t_user` WHERE `id` = 1 AND `username` = 'test' AND ( `status` = 1 AND `integral` > 1 AND ( `password` <> '' ) )");
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
        $this->assertSame($result, "SELECT * FROM `t_user` WHERE id = 1 AND `info` = 'NULL' AND `update_time` = 'NOT NULL' AND `id` IN (1,2,3) AND `id` NOT IN ('4','5') AND `id` BETWEEN 1 AND 2 AND `id` NOT BETWEEN 4 AND 5 AND `name` LIKE '%test%' AND `name` NOT LIKE '%test%' AND `id` EXISTS ( test ) AND `id` NOT EXISTS ( test ) AND (`create_time` > `update_time`)");
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
        $this->assertSame($query->buildSql(), 'SELECT `id`,`name` FROM `test` WHERE `id` = 1');
        $query->where('integral', '>', 100);
        $this->assertSame($query->buildSql(), 'SELECT `id`,`name` FROM `test` WHERE `id` = 1 AND `integral` > 100');
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
        $result = $query->table('t_user')->where('id', $query->table('t_user')->orderBy('id')->value('id'))->update([
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
        $result = $query->table('t_user')->where('id', $query->table('t_user')->orderBy('id')->value('id'))->delete();
        $this->assertTrue($result > 0);
    }

    /**
     * @return void
     */
    public function testPaginate(): void
    {
        $query = $this->newQuery();
        $page = $query->table('t_user')->paginate(page: 2);
        $this->assertTrue($page->count() > 0);
    }

    public function testChunk(): void
    {
        $query = $this->newQuery();
        $result = $query->table('t_user')->chunk(10, function () {
            return true;
        });

        $this->assertTrue($result);
    }
}