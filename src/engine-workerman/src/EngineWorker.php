<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Worker as BaseWorker;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class EngineWorker extends BaseWorker
{
    /**
     * @param Worker $worker
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onWorkerStart(Worker $worker): void
    {
        $this->start($worker->id);
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

        return $this->workerBind($worker,$this);
    }

    /**
     * @param Worker $worker
     * @param object|string $instance
     * @return Worker
     */
    public function workerBind(Worker $worker,object|string $instance): Worker
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
        ];

        foreach ($callbackMap as $name => $method) {
            if (\method_exists($instance,$method)) {
                $worker->{$name} = [$instance,$method];
            }
        }

        return $worker;
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function exceptionHandler(Throwable $e): void
    {
        Worker::stopAll(log: $e->getFile() . '('. $e->getLine() .')' . ':' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    }
}