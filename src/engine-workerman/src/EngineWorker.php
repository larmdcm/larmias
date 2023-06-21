<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Worker as BaseWorker;
use Larmias\Engine\Timer;
use Larmias\Engine\Event;
use Throwable;
use function Larmias\Utils\format_exception;
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
        parent::initialize();
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
                $processTickInterval = $this->getSettings('process_tick_interval', 1);
                Timer::tick($processTickInterval, function () {
                    $this->trigger(Event::ON_WORKER, [$this]);
                });
            }
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
        $worker = new Worker($config['listen'] ?? null, $config['context'] ?? []);

        $propertyMap = [
            'count' => 'worker_num',
            'user' => 'user',
            'group' => 'group',
            'reloadable' => 'reloadable',
            'reusePort' => 'reuse_port',
            'transport' => 'transport',
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
     * @param Throwable $e
     * @return void
     */
    public function handleException(Throwable $e): void
    {
        Worker::stopAll(log: format_exception($e));
    }
}