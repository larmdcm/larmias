<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Constants;
use WeakMap;
use function str_starts_with;

abstract class Server extends Worker
{
    /**
     * @var WeakMap
     */
    protected WeakMap $connectionMap;

    /**
     * @var bool
     */
    protected bool $running = true;

    /**
     * @return void
     */
    public function initServer(): void
    {
        $this->connectionMap = new WeakMap();
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
        $this->connectionMap[$connection] = $connection->getId();
    }

    /**
     * @return void
     */
    public function serverShutdown(): void
    {
    }

    /**
     * @return void
     */
    public function shutdown(): void
    {
        $this->serverShutdown();
        $this->running = false;
        if (isset($this->connectionMap)) {
            foreach ($this->connectionMap as $connection => $id) {
                $connection->close();
            }
            unset($this->connectionMap);
        }
    }
}