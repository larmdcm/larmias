<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Support\Arr;
use Larmias\WorkerS\Events\EventInterface;
use Larmias\WorkerS\Events\{Select,Event};
use Larmias\WorkerS\Process\Manager as ProcessManager;
use Larmias\WorkerS\Task\TaskWorker;
use Larmias\WorkerS\Process\Worker\Worker as ProcessWorker;
use Larmias\WorkerS\Support\Helper;
use Psr\Log\LoggerInterface;
use Larmias\WorkerS\Worker as BaseWorker;
use Larmias\WorkerS\Constants\Event as EventConstant;
use Stringable;
use RuntimeException;

class Manager
{
    /**
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * 进程管理.
     *
     * @var ProcessManager
     */
    protected static ProcessManager $processManager;

    /**
     * @var BaseWorker[]
     */
    protected static array $workers = [];

    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @var array
     */
    protected static array $serviceWorkers = [];

    /**
     * @var TaskWorker[]
     */
    protected static array $taskWorkers = [];

    /**
     * @var EventInterface
     */
    protected static EventInterface $event;

    /**
     * @var LoggerInterface|null
     */
    protected static ?LoggerInterface $logger = null;

    /**
     * @var boolean
     */
    protected static bool $isInit = false;

    /**
     * @var string
     */
    public static string $processTitle = 'worker-s';

    /**
     * @var string
     */
    protected static string $startFile;

    /**
     * @return void
     */
    protected static function init(): void
    {
        if (static::$isInit) {
            return;
        }
        $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        static::$startFile = \end($backtrace)['file'] ?? '';
        static::$processManager = new ProcessManager();
        static::collectServerConfig();
        static::$isInit = true;
    }

    /**
     * @param  array $config
     * @return void
     */
    public static function setConfig(array $config = []): void
    {
        static::$config = \array_merge(static::$config,$config);
    }

    /**
     * @param BaseWorker $worker
     * @return void
     */
    public static function add(BaseWorker $worker)
    {
        static::$workers[$worker->getObjectId()] = $worker;
    }
    
    /**
     * 收集服务配置
     *
     * @return void
     */
    protected static function collectServerConfig(): void
    {
        static::$config['task_worker_num'] = 0;
        static::$config['worker_num'] = 0;
        $workerKey = 0;
        foreach (static::$workers as $worker) {
            $worker->initConfig();
            $itemWorkerNum       = $worker->getConfig('worker_num');
            $itemTaskWorkerNum   = $worker->getConfig('task_worker_num');
            $totalWorkerNum      = $itemWorkerNum + $itemTaskWorkerNum;
            static::$config['task_worker_num'] += $itemTaskWorkerNum;
            static::$config['worker_num'] += $totalWorkerNum;
            for ($i = 1; $i <= $totalWorkerNum; $i++) {
                $isTaskWorker = $i > $itemWorkerNum;
                static::$serviceWorkers[++$workerKey] = [
                    'worker_no'         => $i,
                    'object_id'         => $worker->getObjectId(),
                    'worker_num'        => $itemWorkerNum,
                    'task_worker_num'   => $itemTaskWorkerNum,
                    'is_task_worker'    => $isTaskWorker,
                    'task_worker_no'    => $isTaskWorker ? $i - $itemWorkerNum : -1,
                ];
            }
        }
        static::$processManager->setConfig(static::$config);
        static::$processManager->init();
    }

    /**
     * @return void
     */
    protected static function initTaskWorker(): void
    {
        if (static::$config['task_worker_num'] == 0) {
            return;
        }
        foreach (static::$serviceWorkers as $workerId => $item) {
            if (!$item['is_task_worker']) {
                continue;
            }
            if (!isset(static::$taskWorkers[$item['object_id']])) {
                static::$taskWorkers[$item['object_id']] = [];
            }
            $worker = static::getWorkerById($item['object_id']);
            static::$taskWorkers[$item['object_id']][$item['task_worker_no']] = new TaskWorker($workerId,$worker->getConfig('task',[]));
        }
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected static function runAllWorker(): void
    {
        foreach (static::$workers as $worker) {
            $worker->init();
        }

        static::$processManager->on('masterStart',function () {
            Helper::setProcessTitle(static::getProcessTitle('master process (' .  static::$startFile .')'));
        });

        static::$processManager->on('workerStart',function (ProcessWorker $worker) {
            $serviceWorker = static::getServiceWorkerByWorkerId($worker->getWorkerId());
            $service = static::getWorkerById($serviceWorker['object_id']);
            static::$event = static::makeEvent();
            $worker->setWorkerNo($serviceWorker['worker_no'])->setForceExit(false);
            if ($serviceWorker['is_task_worker']) {
                Timer::init(static::$event);
                $worker->getSignal()->setSignalHandler(static::$event);
                static::$taskWorkers[$serviceWorker['object_id']][$serviceWorker['task_worker_no']]->setEvent(static::$event);
                Helper::setProcessTitle(static::getProcessTitle($service->getName() . ' task process'));
            } else {
                $taskWorker = static::getTaskWorkerFromWorkerId($worker->getWorkerId());
                if ($taskWorker) {
                    $service->setTaskWorker($taskWorker);
                }
                if ($service instanceof Server) {
                    Timer::init(static::$event);
                    $worker->getSignal()->setSignalHandler(static::$event);
                    $service->workerStart($worker,static::$event);
                } else {
                    Helper::setProcessTitle(static::getProcessTitle($service->getName() . ' process'));
                    $service->fireEvent(EventConstant::ON_WORKER_START,$worker);
                }
            }
        });

        static::$processManager->on('worker',function (ProcessWorker $worker) {
            $serviceWorker = static::getServiceWorkerByWorkerId($worker->getWorkerId());
            $service = static::getWorkerById($serviceWorker['object_id']);
            if ($serviceWorker['is_task_worker']) {
                static::$taskWorkers[$serviceWorker['object_id']][$serviceWorker['task_worker_no']]->run();
            } else {
                if ($service instanceof Server) {
                    $service->listen();
                } else {
                    $service->fireEvent(EventConstant::ON_WORKER,$worker);
                }
            }
        });

        static::$processManager->on('workerStop',function (ProcessWorker $worker) {
            $serviceWorker = static::getServiceWorkerByWorkerId($worker->getWorkerId());
            $service = static::getWorkerById($serviceWorker['object_id']);
            if ($serviceWorker['is_task_worker']) {
                static::$taskWorkers[$serviceWorker['object_id']][$serviceWorker['task_worker_no']]->stop();
            } else {
                if ($service instanceof Server) {
                    $service->workerStop($worker);
                } else {
                    $service->fireEvent(EventConstant::ON_WORKER_STOP,$worker);
                }
            }
        });

        static::$processManager->run();
    }

    /**
     * 运行全部服务
     *
     * @param string|array|null $objectId
     * @return void
     * @throws \Throwable
     */
    public static function runAll(string|array $objectId = null): void
    {
        $objectIds = Arr::wrap($objectId);
        if (!empty($objectIds)) {
            static::$workers = \array_filter(static::$workers,fn($id) => \in_array($id,$objectIds),\ARRAY_FILTER_USE_KEY);
        }
        static::init();
        static::initTaskWorker();
        static::runAllWorker();
    }

    /**
     * 停止全部服务
     * 
     * @param ?string $message
     * @return bool
     */
    public static function stopAll(?string $message = null): bool
    {
        static::init();
        if ($message !== null) {
            static::trace($message);
        }
        return static::$processManager->stop();
    }

    /**
     * 重启全部服务
     *
     * @return bool
     */
    public static function reloadAll(): bool
    {
        static::init();
        return static::$processManager->reload();
    }

    /**
     * @param integer $workerId
     * @return array
     */
    protected static function getServiceWorkerByWorkerId(int $workerId): array
    {
        if (!isset(static::$serviceWorkers[$workerId])) {
            throw new RuntimeException('This workerId('. $workerId .') is a binding server');
        }
        return static::$serviceWorkers[$workerId];
    }

    /**
     * @param string $objectId
     * @return BaseWorker
     */
    protected static function getWorkerById(string $objectId): BaseWorker
    {
        if (!isset(static::$workers[$objectId])) {
            throw new RuntimeException('This serverId('. $objectId .') is a binding server');
        }
        return static::$workers[$objectId];
    }

    /**
     * @param integer $workerId
     * @return TaskWorker|null
     */
    protected static function getTaskWorkerFromWorkerId(int $workerId): ?TaskWorker
    {
        $serviceWorker = static::getServiceWorkerByWorkerId($workerId);
        if ($serviceWorker['task_worker_num'] == 0) {
            return null;
        }
        $taskWorkerIndex = ($serviceWorker['worker_no'] % $serviceWorker['task_worker_num']) + 1;
        return static::$taskWorkers[$serviceWorker['object_id']][$taskWorkerIndex];
    }
    
    /**
     * 日志输出或记录.
     *
     * @param string|Stringable $message
     * @param string $level
     * @param array $context
     * @return void
     */
    public static function trace(string|Stringable $message,string $level = 'info',array $context = []): void
    {
        if (!static::$logger) {
            Helper::safeEcho((string)$message . PHP_EOL);
            return;
        }
        static::$logger->log($level,$message,$context);
    }

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        static::$logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        return static::$logger;
    }

    /**
     * @return EventInterface
     */
    public static function makeEvent(): EventInterface
    {
        if (\extension_loaded('event')) {
            return new Event();
        }
        return new Select();
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getProcessTitle(string $name = ''): string
    {
        if ($name === '') {
            return static::$processTitle;
        }
        return static::$processTitle . ': ' . $name;
    }
}