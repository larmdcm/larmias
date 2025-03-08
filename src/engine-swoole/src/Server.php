<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Constants;
use Swoole\Timer;
use WeakMap;
use function str_starts_with;

abstract class Server extends Worker
{
    /**
     * @var WeakMap<ConnectionInterface,int>
     */
    protected WeakMap $connectionMap;

    /**
     * @var bool
     */
    protected bool $running = true;

    /**
     * @var int
     */
    protected int $heartbeatCheckTimerId = 0;

    /**
     * @return void
     */
    public function initServer(): void
    {
        $this->connectionMap = new WeakMap();
        $this->initWaiter();
        if (method_exists($this, 'initIdAtomic')) {
            $this->initIdAtomic();
        }
    }

    /**
     * @return array
     */
    public function getServerSettings(): array
    {
        $settings = $this->getSettings();
        $serverSettings = [];
        $settingMap = [
            'enable_reuse_port' => Constants::OPTION_REUSE_PORT,
            'package_max_length' => Constants::OPTION_PACKAGE_MAX_LENGTH,
            'backlog' => Constants::OPTION_BACKLOG,
        ];

        foreach ($settingMap as $key => $opt) {
            if (isset($settings[$opt])) {
                $serverSettings[$key] = $settings[$opt];
            }
        }

        foreach ($settings as $key => $value) {
            if (str_starts_with($key, 'swoole_')) {
                $serverSettings[substr($key, 7)] = $value;
            }
        }

        return $serverSettings;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function join(ConnectionInterface $connection): void
    {
        if (!isset($this->connectionMap)) {
            return;
        }

        if (isset($this->connectionMap[$connection])) {
            return;
        }

        $this->connectionMap[$connection] = $connection->getId();
    }

    /**
     * @return void
     */
    public function startHeartbeatCheck(): void
    {
        if (!$this->isOpenHeartbeatCheck() || !isset($this->connectionMap)) {
            return;
        }
        $checkInterval = (int)$this->getSettings('heartbeat_check_interval', 60);
        $idleTime = (int)$this->getSettings('heartbeat_idle_time', $checkInterval * 2);
        $ignoreProcessing = (bool)$this->getSettings('heartbeat_ignore_processing', true);
        $this->heartbeatCheckTimerId = Timer::tick($checkInterval * 1000, function () use ($ignoreProcessing, $idleTime) {
            $nowTime = time();
            foreach ($this->connectionMap as $connection => $id) {
                if (!isset($connection->lastHeartbeatTime)) {
                    continue;
                }

                if ($ignoreProcessing && isset($connection->processing) && $connection->processing) {
                    continue;
                }

                if ($nowTime - $connection->lastHeartbeatTime > $idleTime) {
                    $this->closeConnection($connection);
                }
            }
        });
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function connHeartbeatCheck(ConnectionInterface $connection): void
    {
        if (isset($connection->processing)) {
            $connection->processing = true;
        }

        if (!$this->isOpenHeartbeatCheck() || !isset($connection->lastHeartbeatTime)) {
            return;
        }
        $connection->lastHeartbeatTime = time();
        $this->join($connection);
    }

    /**
     * @return bool
     */
    public function isOpenHeartbeatCheck(): bool
    {
        return (bool)$this->getSettings('open_heartbeat_check', false);
    }

    /**
     * @return void
     */
    public function onServerStart(): void
    {
    }

    /**
     * @return void
     */
    public function onServerShutdown(): void
    {
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $this->startHeartbeatCheck();
        $this->onServerStart();
    }

    /**
     * @return void
     */
    public function shutdown(): void
    {
        $this->onServerShutdown();
        $this->running = false;
        $this->heartbeatCheckTimerId && Timer::clear($this->heartbeatCheckTimerId);
        if (isset($this->connectionMap)) {
            foreach ($this->connectionMap as $connection => $id) {
                $this->closeConnection($connection);
            }
            unset($this->connectionMap);
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function closeConnection(ConnectionInterface $connection): void
    {
        $connection->close();
        if (isset($this->connectionMap)) {
            unset($this->connectionMap[$connection]);
        }
    }
}