<?php

declare(strict_types=1);

namespace LarmiasTest\LogDb;

class IndexTest extends TestCase
{
    /**
     * @return void
     */
    public function testOpen(): void
    {
        $this->newIndex()->load();
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $index = $this->newIndex();
        for ($i = 0; $i < 10000; $i++) {
            $index->update($i, $i);
        }
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testOffset(): void
    {
        $index = $this->newIndex();
        $index->load();
        $offset = $index->offset(100, 6000);
        var_dump($offset);
        $this->assertTrue(true);
    }
}