<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\IdentityInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class TokenGuard extends Guard
{
    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @param CacheInterface $cache
     * @return void
     */
    public function initialize(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param IdentityInterface $identity
     * @return bool
     * @throws InvalidArgumentException
     */
    public function login(IdentityInterface $identity): bool
    {
        $this->identity = $identity;
        return $this->cache->set($this->authName, $this->id());
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function logout(): bool
    {
        if ($this->guest()) {
            return false;
        }
        $this->identity = null;
        return $this->cache->delete($this->authName);
    }
}