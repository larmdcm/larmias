<?php

declare(strict_types=1);

namespace LarmiasTest\LogDb;

use Larmias\LogDb\DB;
use Larmias\LogDb\Index;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function newDB(string $name = 'log'): DB
    {
        return new DB($name, [
            'data_dir' => LARMIAS_RUNTIME_PATH
        ]);
    }

    public function newIndex(string $name = 'test'): Index
    {
        return new Index(LARMIAS_RUNTIME_PATH . '/' . $name . '.idx');
    }
}