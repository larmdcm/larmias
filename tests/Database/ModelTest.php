<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

class ModelTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreate(): void
    {
        $username = 'test' . mt_rand(100, 999);
        $model = UserModel::create([
            'username' => $username,
            'password' => '123456'
        ]);

        $this->assertSame($model->username, $username);
    }

    /**
     * @return void
     */
    public function testFind(): void
    {
        $model = UserModel::new()->find(1);
        $this->assertNotEmpty($model);
        $this->assertSame($model->id, 1);
        $this->assertNotSame(-1, $model->integral);
    }

    /**
     * @return void
     */
    public function testSave(): void
    {
        $username = 'test' . mt_rand(100, 999);
        $model = UserModel::new();
        $this->assertFalse($model->isExists());
        $this->assertTrue($model->save([
            'username' => $username,
            'password' => '123456'
        ]));
        $this->assertTrue($model->isExists());
        $this->assertSame($model->username, $username);
        $this->assertSame(-1, $model->integral);
        $model->save([
            'integral' => 1
        ]);
        $this->assertSame(1, $model->integral);
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $model = UserModel::new()->orderBy('id')->firstOrFail();
        $this->assertTrue($model->delete());
        $this->assertFalse($model->isExists());
    }

    /**
     * @return void
     */
    public function testGenUniqueId(): void
    {
        $id = UserTModel::new()->generateUniqueId();
        var_dump($id);
        $this->assertNotEmpty($id);
    }

    /**
     * @return void
     */
    public function testSoftDelete(): void
    {
        $model = UserTModel::new()->where('id', '>', 0)->orderBy('id')->first();
        $this->assertTrue($model->isExists());
        $model->delete();
        $this->assertFalse($model->isExists());
    }
}