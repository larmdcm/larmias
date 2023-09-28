<?php

declare(strict_types=1);

namespace Larmias\JWTAuth;

use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Larmias\JWTAuth\Exceptions\JWTException;
use Larmias\JWTAuth\Exceptions\TokenBlacklistException;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class JWT extends AbstractJWT
{
    /**
     * @var BlacklistInterface
     */
    protected BlacklistInterface $blackList;

    /**
     * @param BlacklistInterface $blackList
     * @return void
     */
    public function initialize(BlacklistInterface $blackList): void
    {
        $this->blackList = $blackList;
    }

    /**
     * 获取TOKEN
     * @param array $claims
     * @return UnencryptedToken
     */
    public function getToken(array $claims): UnencryptedToken
    {
        $config = $this->getCurrSceneConfig();
        $loginType = $config['login_type'];
        $ssoKey = $config['sso_key'];
        if ($loginType == 'mpop') {
            $uniqueId = uniqid($this->getScene() . '_', true);
        } else {
            if (empty($claims[$ssoKey])) {
                throw new JWTException("There is no {$ssoKey} key in the claims", 400);
            }
            $uniqueId = $this->getScene() . "_" . $claims[$ssoKey];
        }

        $signer = new $config['supported_algs'][$config['alg']];
        $time = new \DateTimeImmutable();
        $builder = Configuration::forSymmetricSigner($signer, $this->getKey($config))
            ->builder()
            ->identifiedBy($uniqueId)
            ->issuedAt($time)
            ->canOnlyBeUsedAfter($time)
            ->expiresAt($time->modify(sprintf('+%s second', $config['ttl'])));

        $claims[$this->tokenScenePrefix] = $this->getScene();
        foreach ($claims as $k => $v) {
            $builder = $builder->withClaim($k, $v);
        }

        $token = $builder->getToken($signer, $this->getKey($config));
        if ($loginType == 'sso') {
            $this->blackList->add($token, $config);
        }

        return $token;
    }

    /**
     * 验证token
     * @param string $token
     * @param string|null $scene
     * @param bool $throwException
     * @return bool
     * @throws Throwable
     */
    public function checkToken(string $token, ?string $scene = null, bool $throwException = true): bool
    {
        try {
            $tokenObj = $this->parseToken($token);
            $config = $this->getSceneConfig($scene ?? $this->getScene());

            // 验证token是否存在黑名单
            if ($config['blacklist_enabled'] && $this->blackList->has($tokenObj, $config)) {
                throw new TokenBlacklistException('The token is in blacklist.');
            }
        } catch (Throwable $e) {
            if ($throwException) {
                throw $e;
            }
            return false;
        }

        return true;
    }

    /**
     * 解析token
     * @param string $token
     * @return UnencryptedToken
     */
    public function parseToken(string $token): UnencryptedToken
    {
        $config = $this->getCurrSceneConfig();
        $signer = new $config['supported_algs'][$config['alg']];
        $parser = Configuration::forSymmetricSigner($signer, $this->getKey($config))
            ->parser();
        return $parser->parse($token);
    }

    /**
     * @param array $config
     * @param string $type
     * @return Key|null
     */
    public function getKey(array $config, string $type = 'private'): ?Key
    {
        // 对称算法
        if (in_array($config['alg'], $config['symmetry_algs'])) {
            $key = InMemory::base64Encoded($config['secret']);
        }

        // 非对称
        if (in_array($config['alg'], $config['asymmetric_algs'])) {
            $key = InMemory::base64Encoded($config['keys'][$type]);
        }

        return $key ?? null;
    }

    protected function validateToken(array $config, string $token): bool
    {
        $signer = new $config['supported_algs'][$config['alg']];
        $key = $this->getKey($config);
        $configuration = Configuration::forSymmetricSigner($signer, $key);
        return true;
    }
}