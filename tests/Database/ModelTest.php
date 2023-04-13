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

        var_dump($model->toArray());

        $this->assertSame($model->username, $username);
    }
}