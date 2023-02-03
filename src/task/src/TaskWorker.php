<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\ShareMemory\Client\Client;
use Larmias\Task\Enum\WorkerStatus;

class TaskWorker
{
    /**
     * @var array
     */
    protected array $config = [
        'connect' => '',
    ];

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var int
     */
    protected int $workerId;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $status = WorkerStatus::IDLE;

    /**
     * TaskWorker constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        $this->client = new Client();
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    public function run(): void
    {
    }

    public function __destruct()
    {
    }
}