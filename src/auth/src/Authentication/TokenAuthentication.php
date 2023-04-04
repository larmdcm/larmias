<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class TokenAuthentication extends Authentication
{
    /**
     * @var array
     */
    protected array $config = [
        'name' => 'token',
    ];

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @param CacheInterface $cache
     */
    public function initialize(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param mixed $parameter
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCredentials(mixed $parameter): mixed
    {
        /** @var ServerRequestInterface $parameter */
        return $this->cache->get($parameter->getHeaderLine($this->config['name']));
    }
}