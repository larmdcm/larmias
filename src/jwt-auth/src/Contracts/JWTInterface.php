<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Contracts;

use Lcobucci\JWT\UnencryptedToken;
use Throwable;

interface JWTInterface
{
    /**
     * 获取TOKEN
     * @param array $claims
     * @return UnencryptedToken
     */
    public function getToken(array $claims): UnencryptedToken;

    /**
     * 验证token
     * @param string $token
     * @param bool $throwException
     * @return bool
     * @throws Throwable
     */
    public function checkToken(string $token, bool $throwException = true): bool;

    /**
     * 刷新token
     * @param string $token
     * @return UnencryptedToken
     */
    public function refreshToken(string $token): UnencryptedToken;

    /**
     * 解析token
     * @param string $token
     * @return UnencryptedToken
     */
    public function parseToken(string $token): UnencryptedToken;

    /**
     * 获取当前场景
     * @return string
     */
    public function getScene(): string;

    /**
     * 设置当前场景
     * @param string $scene
     * @return JWTInterface
     */
    public function setScene(string $scene = 'default'): JWTInterface;

    /**
     * 获取场景配置
     * @param string $scene
     * @return array
     */
    public function getSceneConfig(string $scene = 'default'): array;

    /**
     * 设置场景配置
     * @param string $scene
     * @param array $value
     * @return JWTInterface
     */
    public function setSceneConfig(string $scene = 'default', array $value = []): JWTInterface;

    /**
     * 获取当前场景配置
     * @return array
     */
    public function getCurrSceneConfig(): array;
}