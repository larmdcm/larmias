<?php

declare(strict_types=1);

namespace Larmias\JWTAuth;

use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Token\RegisteredClaims;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function time;
use function intval;

class BlackList implements BlacklistInterface
{
    /**
     * @param CacheInterface $cache
     * @return void
     */
    public function __construct(protected CacheInterface $cache)
    {
    }

    /**
     * 添加token到黑名单
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add(UnencryptedToken $token, array $config = []): bool
    {
        if (!$config['blacklist_enabled']) {
            return false;
        }
        $claims = $token->claims()->all();
        $cacheKey = $this->getCacheKey($claims[RegisteredClaims::ID], $config);
        $graceTime = time() + intval($config['blacklist_grace_period'] ?? 0);
        $cacheTime = $claims[RegisteredClaims::EXPIRATION_TIME]->getTimestamp() - time();
        return $this->cache->set($cacheKey, $graceTime, $cacheTime);
    }

    /**
     * 判断token是否在黑名单中
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(UnencryptedToken $token, array $config = []): bool
    {
        if (!$config['blacklist_enabled']) {
            return false;
        }

        $claims = $token->claims()->all();
        $cacheKey = $this->getCacheKey($claims[RegisteredClaims::ID], $config);
        $graceTime = $this->cache->get($cacheKey);
        if (!$graceTime) {
            return false;
        }

        if ($config['login_type'] == 'mpop') {
            return $graceTime >= time();
        }

        if (!is_null($claims[RegisteredClaims::ISSUED_AT])) {
            return $claims[RegisteredClaims::ISSUED_AT]->getTimestamp() - $graceTime >= 0;
        }

        return false;
    }

    /**
     * 将token移出黑名单
     * @param UnencryptedToken $token
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(UnencryptedToken $token, array $config = []): bool
    {
        $claims = $token->claims()->all();
        $cacheKey = $this->getCacheKey($claims[RegisteredClaims::ID], $config);
        return $this->cache->delete($cacheKey);
    }

    /**
     * 获取缓存key
     * @param string $jti
     * @param array $config
     * @return string
     */
    protected function getCacheKey(string $jti, array $config = []): string
    {
        return $config['blacklist_prefix'] . '_' . $jti;
    }
}