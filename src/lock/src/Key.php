<?php

declare(strict_types=1);

namespace Larmias\Lock;

class Key
{
    protected string $prefix = '';

    public function __construct(protected string $name, protected int $ttl)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->prefix . $this->name;
    }
}