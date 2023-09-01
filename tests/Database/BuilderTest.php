<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

use Larmias\Database\Entity\Expression;

class BuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testEscape(): void
    {
        $builder = $this->newBuilder();
        $str = $builder->escapeField('name');
        $this->assertSame($str, '`name`');
        $str = $builder->escapeField('t1.name');
        $this->assertSame($str, '`t1`.`name`');
        $str = $builder->escapeField('t1.name as tname');
        $this->assertSame($str, '`t1`.`name` as `tname`');
        $str = $builder->escapeField('*');
        $this->assertSame($str, '*');
        $str = $builder->escapeField('t1.*');
        $this->assertSame($str, '`t1`.*');
    }

    /**
     * @return void
     */
    public function testParseField(): void
    {
        $builder = $this->newBuilder();
        $field = $builder->parseField([
            new Expression('?', ['a1']),
            ['a6', 'a9'],
            's1',
            1,
            ['a12' => 'as12', 'a16' => 'as19']
        ]);
        $this->assertSame($field, '?,`a6`,`a9`,`s1`,1,`as12` AS `a12`,`as19` AS `a16`');
    }

    /**
     * @return void
     */
    public function testParseTable(): void
    {
        $builder = $this->newBuilder();
        $table = $builder->parseTable('test');
        $this->assertSame($table, '`test`');
        $tableAlias = $builder->parseTable('test', ['test' => 't']);
        $this->assertSame($tableAlias, '`test` AS `t`');
        $this->assertSame($builder->parseTable(['a' => 'b', 'test' => 't']), '`a` AS `b`,`test` AS `t`');
        $this->assertSame($builder->parseTable(['a', 'b', 'c']), '`a`,`b`,`c`');
        $this->assertSame($builder->parseTable(['a a1', 'b b1']), '`a` `a1`,`b` `b1`');
        $this->assertSame($builder->parseTable(['(select * from a)' => 'a1']), '(select * from a) AS `a1`');
    }

    /**
     * @return void
     */
    public function testParseSubTable(): void
    {
        $builder = $this->newBuilder();
        $table = $builder->parseTable([
            '(select * from `page`)' => 'page'
        ]);

        $this->assertSame($table, '(select * from `page`) AS `page`');
    }

    /**
     * @return void
     */
    public function testParseWhere(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseWhere([
            'AND' => [
                ['username', '=', 'test'],
                ['integral', '>', 10],
                ['status', '<', 1],
            ],
            'OR' => [
                ['password', '=', '']
            ]
        ]);
        $this->assertSame($result, ' WHERE `username` = ? AND `integral` > ? AND `status` < ? OR `password` = ?');
    }

    /**
     * @return void
     */
    public function testParseJoin(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseJoin([
            ['user_info i', ['i.user_id', '=', 'u.id'], 'LEFT'],
            ['article a', ['a.user_id', 'u.id'], 'LEFT'],
        ]);
        $this->assertSame($result, ' LEFT JOIN `user_info` `i` ON `i`.`user_id` = `u`.`id` LEFT JOIN `article` `a` ON `a`.`user_id` = `u`.`id`');
    }

    /**
     * @return void
     */
    public function testParseGroup(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseGroup(['a', 'b', 'c']);
        $this->assertSame($result, ' GROUP BY `a`,`b`,`c`');
    }

    /**
     * @return void
     */
    public function testParseOrder(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseOrder([
            ['a' => 'DESC'],
            ['b' => 'ASC'],
        ]);
        $this->assertSame($result, ' ORDER BY `a` DESC,`b` ASC');
    }

    /**
     * @return void
     */
    public function testParseHaving(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseHaving([
            'AND' => [
                'count > 10'
            ],
            'OR' => [
                'num = 10'
            ]
        ]);

        $this->assertSame($result, ' Having count > 10 OR num = 10');
    }

    /**
     * @return void
     */
    public function testParseLimit(): void
    {
        $builder = $this->newBuilder();
        $result = $builder->parseLimit(10, 20);
        $this->assertSame($result, ' LIMIT ?,?');
        $this->assertSame($builder->parseLimit(10), ' LIMIT ?');
    }
}