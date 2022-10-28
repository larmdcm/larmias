<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Events\EventInterface;
use Larmias\WorkerS\Events\{Select,Event};
use Larmias\WorkerS\Process\Manager as ProcessManager;
use Larmias\WorkerS\Task\TaskWorker;
use Larmias\WorkerS\Process\Worker\Worker;
use Larmias\WorkerS\Support\Helper;
use RuntimeException;
use Psr\Log\LoggerInterface;
use Stringable;

class WorkerS
{
    /**
     * 进程管理.
     *
     * @var ProcessManager
     */
    protected static ProcessManager $processManager;

    /**
     * @var Server[]
     */
    protected static array $servers = [];

    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @var array
     */
    protected static array $serverWorkers = [];

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
     * @param Server $server
     * @return void
     */
    public static function addServer(Server $server)
    {
        static::$servers[$server->getServerId()] = $server;
    }
    
    /**
     * 收集服务配置
     *
     * @return void
     */
    protected static function collectServerConfig(): void
    {
        $workerNum = 0;
        static::$config['task_worker_num'] = 0;
        foreach (static::$servers as $server) {
            $server->initConfig();
            $serverWorkerNum     = $server->getConfig('worker_num');
            $serverTaskWorkerNum = $server->getConfig('task_worker_num');
            $totalWorkerNum      = $serverWorkerNum + $serverTaskWorkerNum;
            static::$config['task_worker_num'] += $serverTaskWorkerNum;
            for ($i = 1; $i <= $totalWorkerNum; $i++) {
                $workerNum++;
                $isTaskWorker = $i > $serverWorkerNum;
                static::$serverWorkers[$workerNum] = [
                    'worker_no'         => $i,
                    'server_id'         => $server->getServerId(),
                    'worker_num'        => $serverWorkerNum,
                    'task_worker_num'   => $serverTaskWorkerNum,
                    'is_task_worker'    => $isTaskWorker,
                    'task_worker_no'    => $isTaskWorker ? $i - $serverWorkerNum : -1,
                ];
            }
        }
        static::$config['worker_num'] = $workerNum;
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
        foreach (static::$serverWorkers as $workerId => $item) {
            if (!$item['is_task_worker']) {
                continue;
            }
            if (!isset(static::$taskWorkers[$item['server_id']])) {
                static::$taskWorkers[$item['server_id']] = [];
            }
            $server = static::getServerById($item['server_id']);
            static::$taskWorkers[$item['server_id']][$item['task_worker_no']] = new TaskWorker($workerId,$server->getConfig('task',[]));
        }
    }

    /**
     * @return void
     */
    protected static function runAllServer(): void
    {
        foreach (static::$servers as $server) {
            $server->init();
        }

        static::$processManager->on('masterStart',function () {
            Helper::setProcessTitle(static::getProcessTitle('master process (' .  static::$startFile .')'));
        });

        static::$processManager->on('workerStart',function (Worker $worker) {
            $serverWorker = static::getServerWorkerByWorkerId($worker->getWorkerId());
            $server = static::getServerById($serverWorker['server_id']);
            static::$event = static::makeEvent();
            Timer::init(static::$event);
            $worker->setWorkerNo($serverWorker['worker_no'])->setForceExit(false)->getSignal()->setSignalHandler(static::$event);
            if ($serverWorker['is_task_worker']) {
                static::$taskWorkers[$serverWorker['server_id']][$serverWorker['task_worker_no']]->setEvent(static::$event);
                Helper::setProcessTitle(static::getProcessTitle($server->getName() . ' task worker process'));
            } else {
                $taskWorker = static::getTaskWorkerFromWorkerId($worker->getWorkerId());
                if ($taskWorker) {
                    $server->setTaskWorker($taskWorker);
                }
                $server->workerStart($worker,static::$event);
            }
        });

        static::$processManager->on('worker',function (Worker $worker) {
            $serverWorker = static::getServerWorkerByWorkerId($worker->getWorkerId());
            $server = static::getServerById($serverWorker['server_id']);
            if ($serverWorker['is_task_worker']) {
                static::$taskWorkers[$serverWorker['server_id']][$serverWorker['task_worker_no']]->run();
            } else {
                $server->listen();
            }
        });

        static::$processManager->on('workerStop',function (Worker $worker) {
            $serverWorker = static::getServerWorkerByWorkerId($worker->getWorkerId());
            $server = static::getServerById($serverWorker['server_id']);
            if ($serverWorker['is_task_worker']) {
                static::$taskWorkers[$serverWorker['server_id']][$serverWorker['task_worker_no']]->stop();
            } else {
                $server->workerStop($worker);
            }
        });

        static::$processManager->run();
    }

    /**
     * 运行全部服务
     *
     * @param string|null $serverId
     * @return void
     */
    public static function runAll(?string $serverId = null): void
    {
        if ($serverId !== null) {
            static::$servers = \array_filter(static::$servers,fn($sid) => $sid === $serverId,\ARRAY_FILTER_USE_KEY);
        }
        static::init();
        static::initTaskWorker();
        static::runAllServer();
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
    protected static function getServerWorkerByWorkerId(int $workerId): array
    {
        if (!isset(static::$serverWorkers[$workerId])) {
            throw new RuntimeException('This workerId('. $workerId .') is a binding server');
        }
        return static::$serverWorkers[$workerId];
    }

    /**
     * @param string $serverId
     * @return Server
     */
    protected static function getServerById(string $serverId): Server
    {
        if (!isset(static::$servers[$serverId])) {
            throw new RuntimeException('This serverId('. $serverId .') is a binding server');
        }
        return static::$servers[$serverId];
    }

    /**
     * @param integer $workerId
     * @return TaskWorker|null
     */
    protected static function getTaskWorkerFromWorkerId(int $workerId): ?TaskWorker
    {
        $serverWorker    = static::getServerWorkerByWorkerId($workerId);
        if ($serverWorker['task_worker_num'] == 0) {
            return null;
        }
        $taskWorkerIndex =  ($serverWorker['worker_no'] % $serverWorker['task_worker_num']) + 1;
        return static::$taskWorkers[$serverWorker['server_id']][$taskWorkerIndex];
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