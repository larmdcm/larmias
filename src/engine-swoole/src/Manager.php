<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Swoole\Process\Pool;
use Swoole\Constant;
use Swoole\Runtime;
use function count;
use const SWOOLE_IPC_UNIXSOCK;

class Manager
{
    /**
     * @param array $workers
     */
    public function __construct(protected array $workers = [])
    {
    }

    /**
     * @param callable $callback
     * @param int $workerNum
     * @param string|null $name
     * @return self
     */
    public function addWorker(callable $callback, int $workerNum = 1, ?string $name = null): self
    {
        for ($i = 0; $i < $workerNum; $i++) {
            $this->workers[] = ['callback' => $callback, 'name' => $name];
        }
        return $this;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $pool = new Pool(count($this->workers), SWOOLE_IPC_UNIXSOCK, 0, true);
        $pool->on(Constant::EVENT_WORKER_START, function (Pool $pool, int $workerId) {
            Runtime::enableCoroutine();
            $worker = $this->workers[$workerId];
            $worker['callback']();
        });
        $pool->start();
    }
}