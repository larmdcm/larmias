<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use ArrayObject;

interface CoroutineInterface
{
    /**
     * @param callable $callable
     * @param ...$params
     * @return CoroutineInterface
     */
    public static function create(callable $callable, ...$params): CoroutineInterface;

    /**
     * @param ...$params
     * @return CoroutineInterface
     */
    public function execute(...$params): CoroutineInterface;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return int
     */
    public static function id(): int;

    /**
     * @param int|null $id
     * @return int
     */
    public static function pid(?int $id = null): int;

    /**
     * @param array $config
     * @return void
     */
    public static function set(array $config): void;

    /**
     * @param callable $callable
     * @return void
     */
    public static function defer(callable $callable): void;

    /**
     * @param int|null $id
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject;
}