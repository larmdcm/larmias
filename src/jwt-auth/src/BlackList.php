<?php

declare(strict_types=1);

namespace Larmias\JWTAuth;

use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Token\RegisteredClaims;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function time;

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
        $expTime = $claims[RegisteredClaims::EXPIRATION_TIME];
        $cacheTime = time() - $expTime;
        return $this->cache->set($cacheKey, ['valid_until' => $expTime], $cacheTime);
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
        $claims = $token->claims()->all();
        $cacheKey = $this->getCacheKey($claims[RegisteredClaims::ID], $config);
        $result = $this->cache->get($cacheKey);
        if (!$result) {
            return false;
        }

        if ($config['login_type'] == 'mpop') {
            return time() - $result['valid_until'] >= 0;
        }

        if (!is_null($claims[RegisteredClaims::ISSUED_AT])) {
            return $claims[RegisteredClaims::ISSUED_AT] - $result['valid_until'] >= 0;
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