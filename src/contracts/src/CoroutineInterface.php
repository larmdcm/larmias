<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use ArrayObject;

interface CoroutineInterface
{
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
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject;
}