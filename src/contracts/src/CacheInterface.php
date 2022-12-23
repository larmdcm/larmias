<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Psr\SimpleCache\CacheInterface as BaseCacheInterface;

interface CacheInterface extends BaseCacheInterface
{
    /**
     * 自增
     *
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function increment(string $key, int $step = 1): ?int;

    /**
     * 自减
     *
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function decrement(string $key, int $step = 1): ?int;

    /**
     * 不存在写缓存
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $ttl
     * @return mixed
     */
    public function remember(string $key,mixed $value,mixed $ttl = null): mixed;
}