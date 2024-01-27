<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Constants;
use Larmias\Engine\Worker as BaseWorker;
use Larmias\Engine\Timer;
use Larmias\Engine\Event;
use Throwable;
use function Larmias\Support\format_exception;
use function method_exists;

class EngineWorker extends BaseWorker
{
    /**
     * @var Worker
     */
    protected Worker $worker;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->worker = $this->makeWorker($this->getMakeWorkerConfig());
    }

    /**
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker): void
    {
        try {
            $this->start($worker->id);
            if ($this->hasListen(Event::ON_WORKER)) {
                $processTickInterval = $this->getSettings(Constants::OPTION_PROCESS_TICK_INTERVAL, 1);
                Timer::tick($processTickInterval, function () {
                    $this->trigger(Event::ON_WORKER, [$this]);
                });
            }
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function onWorkerStop(): void
    {
        try {
            $this->stop();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return array
     */
    protected function getMakeWorkerConfig(): array
    {
        return $this->getSettings();
    }

    /**
     * @param array $config
     * @return Worker
     */
    public function makeWorker(array $config = []): Worker
    {
        $context = $config[Constants::OPTION_RAW_CONTEXT] ?? [];

        if (!empty($config[Constants::OPTION_BACKLOG])) {
            $context['socket']['backlog'] = $config[Constants::OPTION_BACKLOG];
        }

        $worker = new Worker($config['listen'] ?? null, $context);

        $propertyMap = [
            'count' => Constants::OPTION_WORKER_NUM,
            'user' => Constants::OPTION_USER,
            'group' => Constants::OPTION_GROUP,
            'reusePort' => Constants::OPTION_REUSE_PORT,
            'transport' => Constants::OPTION_TRANSPORT,
            'reloadable' => 'reloadable',
            'protocol' => 'protocol',
        ];

        foreach ($propertyMap as $property => $key) {
            if (isset($config[$key])) {
                $worker->{$property} = $config[$key];
            }
        }

        return $this->workerBind($worker, $this);
    }

    /**
     * @param Worker $worker
     * @param object|string $instance
     * @return Worker
     */
    public function workerBind(Worker $worker, object|string $instance): Worker
    {
        $callbackMap = [
            'onWorkerStart' => 'onWorkerStart',
            'onConnect' => 'onConnect',
            'onMessage' => 'onMessage',
            'onClose' => 'onClose',
            'onError' => 'onError',
            'onBufferFull' => 'onBufferFull',
            'onBufferDrain' => 'onBufferDrain',
            'onWorkerStop' => 'onWorkerStop',
            'onWebSocketConnect' => 'onWebSocketConnect',
            'onWebSocketClose' => 'onWebSocketClose',
        ];

        foreach ($callbackMap as $name => $method) {
            if (method_exists($instance, $method)) {
                $worker->{$name} = [$instance, $method];
            }
        }

        return $worker;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->getWorkerConfig()->getType();
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function handleException(Throwable $e): void
    {
        Worker::stopAll(log: format_exception($e));
    }
}