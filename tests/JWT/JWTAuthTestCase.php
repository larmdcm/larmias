<?php

declare(strict_types=1);

namespace LarmiasTest\JWT;

class JWTAuthTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testGetToken(): void
    {
        $token = $this->getJWT()->getToken(['uid' => 1]);
        $pToken = $this->getJWT()->parseToken($token->toString());
        $this->assertSame($token->claims()->get('uid'), $pToken->claims()->get('uid'));
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testCheckToken(): void
    {
        $token = $this->getJWT()->getToken(['uid' => 1]);
        $this->assertTrue($this->getJWT()->checkToken($token->toString()));
    }

    /**
     * @return void
     */
    public function testRefreshToken(): void
    {
        $token = $this->getJWT()->getToken(['uid' => 1]);
        $pToken = $this->getJWT()->refreshToken($token->toString());
        $this->assertSame($token->claims()->get('uid'), $pToken->claims()->get('uid'));
    }

    /**
     * @return void
     */
    public function testBlacklist(): void
    {
        $blacklist = $this->getBlacklist();
        $config = $this->getJWT()->getCurrSceneConfig();
        $token = $this->getJWT()->getToken(['uid' => 1]);
        $this->assertTrue($blacklist->add($token, $config));
        $this->assertTrue($blacklist->has($token, $config));
        $this->assertTrue($blacklist->delete($token, $config));
        $this->assertTrue(!$blacklist->has($token, $config));
    }
}