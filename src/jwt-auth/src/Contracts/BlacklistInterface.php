<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Contracts;

use Lcobucci\JWT\UnencryptedToken;

interface BlacklistInterface
{
    /**
     * 添加token到黑名单
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     */
    public function add(UnencryptedToken $token, array $config = []): bool;

    /**
     * 判断token是否在黑名单中
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     */
    public function has(UnencryptedToken $token, array $config = []): bool;

    /**
     * 将token移出黑名单
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     */
    public function delete(UnencryptedToken $token, array $config = []): bool;
}