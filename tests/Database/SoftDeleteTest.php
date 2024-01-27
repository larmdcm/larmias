<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

use LarmiasTest\Database\Model\UserSoftDelModel;

class SoftDeleteTest extends TestCase
{
    /**
     * @return void
     */
    public function testDelete(): void
    {
        $model = UserSoftDelModel::first();
        $this->assertFalse($model->trashed());
        $model->delete();
        $this->assertTrue($model->trashed());
    }

    /**
     * @return void
     */
    public function testRestore(): void
    {
        /** @var UserSoftDelModel $model */
        $model = UserSoftDelModel::onlyTrashed()->first();
        $this->assertTrue($model->trashed());
        $this->assertTrue($model->restore());
        $this->assertFalse($model->trashed());
    }

    /**
     * @return void
     */
    public function testForceDelete(): void
    {
        /** @var UserSoftDelModel $model */
        $model = UserSoftDelModel::orderByRaw('id DESC')->first();
        $this->assertTrue($model->forceDelete(true)->delete());
    }
}