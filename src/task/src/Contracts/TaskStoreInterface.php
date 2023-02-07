<?php

declare(strict_types=1);

namespace Larmias\Task\Contracts;

use Larmias\Task\Task;

interface TaskStoreInterface
{
    /**
     * @param Task $task
     * @return bool
     */
    public function add(Task $task): bool;

    /**
     * @return Task|null
     */
    public function pop(): ?Task;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param int $id
     * @param string $name
     * @return bool
     */
    public function subscribe(int $id, string $name): bool;

    /**
     * @param int $id
     * @param string|null $key
     * @return mixed
     */
    public function getInfo(int $id, ?string $key = null): mixed;

    /**
     * @param int $id
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setInfo(int $id, string $key, mixed $value): bool;

    /**
     * @return array
     */
    public function online(): array;

    /**
     * @param int $id
     * @return int
     */
    public function leave(int $id): int;
}