<?php

declare(strict_types=1);


namespace LarmiasTest\JsonRpc\Service;

use LarmiasTest\JsonRpc\Contracts\UserServiceInterface;

class UserService implements UserServiceInterface
{
    /**
     * 获取用户信息
     * @param int|string $id
     * @return array
     */
    public function getUserInfo(int|string $id): array
    {
        return [
            'id' => $id,
            'username' => mt_rand(1, 666),
        ];
    }
}