<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Tests\Database\Model\UserModel;
use Larmias\Tests\Database\Model\UserScopeModel;
use Larmias\Tests\Database\Model\UserTModel;
use Larmias\Tests\Database\Model\UserInfoModel;

class ModelTest extends TestCase
{
    /**
     * @return void
     */
    public function testInsert(): void
    {
        $username = 'test' . mt_rand(100, 999);
        $result = UserModel::insert([
            'username' => $username,
            'password' => '123456'
        ]);

        $this->assertTrue($result > 0);

        $username = 'test' . mt_rand(100, 999);
        $result = UserModel::insertGetId([
            'username' => $username,
            'password' => '123456'
        ]);

        var_dump($result);

        $this->assertNotEmpty($result);

        $result = UserModel::insertAll([
            ['username' => 'test' . mt_rand(100, 999), 'password' => '123456'],
            ['username' => 'test' . mt_rand(100, 999), 'password' => '123456'],
            ['username' => 'test' . mt_rand(100, 999), 'password' => '123456'],
        ]);

        $this->assertTrue($result > 0);
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $username = 'test' . mt_rand(100, 999);
        $result = UserModel::where('id', 1)->update([
            'username' => $username,
            'password' => '123456'
        ]);

        $this->assertTrue($result > 0);
    }

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
        $model = UserModel::find(1);
        $this->assertNotEmpty($model);
        $this->assertSame($model->id, 1);
        $this->assertNotSame(0, $model->integral);
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
        $model = UserModel::orderBy('id', 'DESC')->firstOrFail();
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
        $model = UserTModel::where('id', '>', 0)->orderBy('id', 'DESC')->first();
        $this->assertTrue($model->isExists());
        $model->delete();
        $this->assertFalse($model->isExists());
    }

    /**
     * @return void
     */
    public function testHiddenAndGuard(): void
    {
        $model = UserTModel::create([
            'username' => mt_rand(111111, 666666),
            'password' => '123456',
            'status' => 0,
        ]);

        var_dump($model->toArray());

        $this->assertNotEquals(0, UserTModel::find($model->id)->status);
    }

    /**
     * @return void
     */
    public function testUserInfoSave(): void
    {
        $model = UserModel::first();
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
        $model = UserModel::first();
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
        $model = UserModel::first();
        $age = mt_rand(22, 35);
        $model->userInfo->save(['age' => $age]);
        $this->assertSame($age, $model->userInfo->age);
    }

    /**
     * @return void
     */
    public function testHasOneWith(): void
    {
        $list = UserModel::with(['userInfo' => function ($query) {
            $query->field('id,user_id');
        }])->get();
        $this->assertSame($list[0]->id, $list[0]->userInfo->user_id);

        $data = UserModel::with(['userInfo' => function ($query) {
            $query->field('id,user_id');
        }])->first();
        $this->assertSame($data->id, $data->userInfo->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToWith(): void
    {
        $list = UserInfoModel::new()->with(['user' => function ($query) {
            $query->field('id');
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
        $list = UserModel::with(['messages' => function ($model) {
            $model->field('id,user_id');
        }])->get();
        $this->assertSame($list[0]->id, $list[0]->messages[0]->user_id);

        $data = UserModel::with(['messages' => function ($model) {
            $model->field('id,user_id');
        }])->first();
        $this->assertSame($data->id, $data->messages[0]->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToManySave(): void
    {
        $user = UserModel::find(1);
        $pivotList = $user->roles()->save([1, 2, 3]);
        $this->assertTrue(count($pivotList) > 0);
        $this->assertSame($user->id, $pivotList[0]->user_id);
    }

    /**
     * @return void
     */
    public function testBelongsToManyGet(): void
    {
        $user = UserModel::find(1);
        var_dump($user->roles[0]->toArray());
        $this->assertTrue($user->roles->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testBelongsToManyWith(): void
    {
        $userList = UserModel::with(['roles'])->where('id', 1)->get();
        $this->assertTrue($userList[0]->roles->isNotEmpty());
    }

    /**
     * @return void
     */
    public function testBelongsToManyDel(): void
    {
        $user = UserModel::find(1);
        $count = $user->roles()->detach();
        $this->assertTrue($count > 0);
    }

    /**
     * @return void
     */
    public function testScope(): void
    {
        $user = UserScopeModel::scope('username', 'test363')->first();
        $this->assertSame($user->username, 'test363');
    }
}