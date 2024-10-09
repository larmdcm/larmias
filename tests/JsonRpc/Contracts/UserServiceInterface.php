<?php

declare(strict_types=1);

namespace LarmiasTest\JsonRpc\Contracts;

interface UserServiceInterface
{
    /**
     * 获取用户信息
     * @param int|string $id
     * @return array
     */
    public function getUserInfo(int|string $id): array;
}