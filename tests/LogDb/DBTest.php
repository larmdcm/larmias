<?php

declare(strict_types=1);

namespace LarmiasTest\LogDb;

class DBTest extends TestCase
{
    public function testLoad(): void
    {
        $db = $this->newDB();
        $db->load();
        $this->assertTrue(true);
    }

    public function testRecord(): void
    {
        $db = $this->newDB();
        $db->load();
        $db->record(session_create_id());
        $this->assertTrue(true);
    }

    public function testQuery(): void
    {
        $db = $this->newDB();
        $db->load();
        $data = $db->query([0, time()]);
        var_dump(iterator_to_array($data));
        $this->assertTrue(true);
    }

    public function testBigRecord(): void
    {
        $db = $this->newDB();
        $db->load();

        for ($i = 0; $i < 100000; $i++) {
            $db->record(session_create_id(), time() + $i * 60);
        }

        $this->assertTrue(true);
    }

    public function testTimeQuery(): void
    {
        $db = $this->newDB();
        $db->load();
        $data = $db->query([1705584976, 1705585276]);
        var_dump(iterator_to_array($data));
        $this->assertTrue(true);
    }
}