<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Tests\Database\Model\UserModel;
use Larmias\Tests\Database\Model\UserTModel;
use Larmias\Tests\Database\Model\UserInfoModel;

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

    /**
     * @return void
     */
    public function testUserInfoSave(): void
    {
        $model = UserModel::new()->first();
        $model->userInfo()->save([
            'age' => mt_rand(22, 35),
            'address' => date('YmdHis'),
        ]);
        $this->assertSame($model->id, $model->userInfo->user_id);
    }

    /**
     * @return void
     */
    public function testUserInfoGet(): void
    {
        /** @var UserModel $model */
        $model = UserModel::new()->first();
        $this->assertSame($model->id, $model->userInfo->user_id);
        $userInfo = $model->userInfo()->field('id,user_id,address')->where('user_id', $model->id)->first();
        $this->assertSame($model->id, $userInfo->user_id);
        $address = date('YmdHis');
        $userInfo->save(['address' => $address]);
        $this->assertSame($address, $userInfo->address);
    }

    /**
     * @return void
     */
    public function testUserInfoUpdate(): void
    {
        $model = UserModel::new()->first();
        $age = mt_rand(22, 35);
        $model->userInfo->save(['age' => $age]);
        $this->assertSame($age, $model->userInfo->age);
    }

    /**
     * @return void
     */
    public function testHasOneWith(): void
    {
        $list = UserModel::new()->with(['userInfo' => function ($model) {
            $model->field('id,user_id');
        }])->get();
        $this->assertSame($list[0]->id, $list[0]->userInfo->user_id);

        $data = UserModel::new()->with(['userInfo' => function ($model) {
            $model->field('id,user_id');
        }])->first();
        $this->assertSame($data->id, $data->userInfo->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToWith(): void
    {
        $list = UserInfoModel::new()->with(['user' => function ($model) {
            $model->field('id');
        }])->get();
        $this->assertSame($list[0]->user_id, $list[0]->user->id);

        $data = UserInfoModel::new()->with(['user' => function ($model) {
            $model->field('id');
        }])->first();
        $this->assertSame($data->user_id, $data->user->id);
    }

    /**
     * @return void
     */
    public function testHasManyWith(): void
    {
        $list = UserModel::new()->with(['messages' => function ($model) {
            $model->field('id,user_id');
        }])->get();
        $this->assertSame($list[0]->id, $list[0]->messages[0]->user_id);

        $data = UserModel::new()->with(['messages' => function ($model) {
            $model->field('id,user_id');
        }])->first();
        $this->assertSame($data->id, $data->messages[0]->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToManySave(): void
    {
        /** @var UserModel $user */
        $user = UserModel::new()->find(1);
        $pivotList = $user->roles()->save([1, 2, 3]);
        $this->assertTrue(count($pivotList) > 0);
        $this->assertSame($user->id, $pivotList[0]->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToManyGet(): void
    {
        /** @var UserModel $user */
        $user = UserModel::new()->find(1);
        $this->assertTrue($user->roles->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testBelongsToManyWith(): void
    {
        $userList = UserModel::new()->with(['roles'])->get();
        $this->assertTrue($userList[0]->roles->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testBelongsToManyDel(): void
    {
        /** @var UserModel $user */
        $user = UserModel::new()->find(1);
        $count = $user->roles()->detach();
        $this->assertTrue($count > 0);
    }
}