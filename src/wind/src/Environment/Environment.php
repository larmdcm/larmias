<?php

declare(strict_types=1);

namespace Larmias\Wind\Environment;

use Larmias\Wind\Object\ObjectInterface;

class Environment
{
    /**
     * @var ObjectInterface[]
     */
    protected array $store = [];

    /**
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * @param string $name
     * @return ObjectInterface|null
     */
    public function get(string $name): ?ObjectInterface
    {
        return $this->store[$name] ?? null;
    }

    /**
     * @param string $name
     * @param ObjectInterface $object
     * @return ObjectInterface
     */
    public function set(string $name, ObjectInterface $object): ObjectInterface
    {
        $this->store[$name] = $object;
        return $object;
    }
}