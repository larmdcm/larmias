<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface WorkerConfigInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return WorkerConfigInterface
     */
    public function setName(string $name): WorkerConfigInterface;

    /**
     * @return string|null
     */
    public function getHost(): ?string;

    /**
     * @param string|null $host
     * @return WorkerConfigInterface
     */
    public function setHost(?string $host): WorkerConfigInterface;

    /**
     * @return int|null
     */
    public function getPort(): ?int;

    /**
     * @param int|null $port
     * @return WorkerConfigInterface
     */
    public function setPort(?int $port): WorkerConfigInterface;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param int $type
     * @return WorkerConfigInterface
     */
    public function setType(int $type): WorkerConfigInterface;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param array $settings
     * @return WorkerConfigInterface
     */
    public function setSettings(array $settings): WorkerConfigInterface;

    /**
     * @return array
     */
    public function getCallbacks(): array;

    /**
     * @param array $callbacks
     * @return WorkerConfigInterface
     */
    public function setCallbacks(array $callbacks): WorkerConfigInterface;
}