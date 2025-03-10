<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\Client\Http\ClientFactoryInterface as HttpClientFactoryInterface;
use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Client\Http\ClientFactory as HttpClientFactory;
use Larmias\Engine\Swoole\Concerns\WithWaiter;
use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Worker as BaseWorker;
use Swoole\Coroutine\Channel;
use Swoole\Process as SwooleProcess;
use Swoole\Coroutine;
use Throwable;
use function Larmias\Support\format_exception;
use function Larmias\Support\println;
use const SIGTERM;

abstract class Worker extends BaseWorker implements WorkerInterface
{
    use WithWaiter;

    /**
     * @var array
     */
    protected static array $data = [
        'initBind' => false,
        'initReset' => false,
    ];

    /**
     * @param int $workerId
     * @return void
     */
    public function workerStart(int $workerId): void
    {
        try {
            $this->run($workerId);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return void
     */
    public function workerStop(): void
    {
        try {
            $this->stop();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function bind(): void
    {
        if (static::$data['initBind']) {
            return;
        }
        static::$data['initBind'] = true;
        parent::bind();
        $this->container->bindIf([
            HttpClientFactoryInterface::class => HttpClientFactory::class,
        ]);
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        if (static::$data['initReset']) {
            return;
        }
        static::$data['initReset'] = true;
        parent::reset();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getWorkerConfig()->getName();
    }

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return max(1, (int)$this->getSettings('worker_num', 1));
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->getWorkerConfig()->getType();
    }

    /**
     * 等待运行结束
     * @param callable $callback
     * @return void
     */
    public function wait(callable $callback): void
    {
        $quit = new Channel(1);
        Coroutine::create(function () use ($quit) {
            Coroutine::defer(fn() => $quit->close());
            while (ProcessManager::isRunning()) {
                $this->timespan();
            }
        });
        $quit->pop();
        call_user_func($callback);
        if (isset($this->waiter)) {
            $this->waiter->wait(fn() => $this->waiter->done());
        }
    }

    /**
     * @return void
     */
    public function timespan(): void
    {
        $time = (int)$this->getSettings(Constants::OPTION_PROCESS_TICK_INTERVAL, 1);
        $time = max(1, $time);
        usleep($time * 1000);
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function printException(Throwable $e): void
    {
        println(format_exception($e));
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function handleException(Throwable $e): void
    {
        $this->printException($e);
        if (function_exists('posix_getppid')) {
            SwooleProcess::kill(posix_getppid(), SIGTERM);
        }
    }
}