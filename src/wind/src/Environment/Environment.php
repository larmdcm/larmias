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

    public function __construct(protected ?Environment $outer = null)
    {
    }

    /**
     * @return static
     */
    public static function new(?Environment $outer = null): static
    {
        return new static($outer);
    }

    /**
     * @param string $name
     * @return ObjectInterface|null
     */
    public function get(string $name): ?ObjectInterface
    {
        if (!isset($this->store[$name]) && $this->outer !== null) {
            return $this->outer->get($name);
        }

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