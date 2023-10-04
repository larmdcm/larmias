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
}