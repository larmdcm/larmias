<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Manager;
use Larmias\Di\Container;
use PHPUnit\Framework\TestCase;
use Larmias\Database\Contracts\QueryInterface;

class QueryTest extends TestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager(Container::getInstance(), require __DIR__ . '/database.php');
    }

    /**
     * @return void
     */
    public function testSelect(): void
    {
        $query = $this->getQuery();

        $query->table('test')->field('id,name')->where('id', 1);
        $this->assertSame($query->buildSql(), 'SELECT`id`,`name` FROM `test` WHERE `id` = \'1\'');
        $query->where('integral', '>', 100);
        $this->assertSame($query->buildSql(), 'SELECT`id`,`name` FROM `test` WHERE `id` = \'1\' AND `integral` > \'100\'');
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testQuery(): void
    {
        $query = $this->getQuery();
    }


    /**
     * @return QueryInterface
     */
    protected function getQuery(): QueryInterface
    {
        return $this->manager->query();
    }
}