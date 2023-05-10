<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Event;
use function str_starts_with;
use Throwable;

abstract class Server extends Worker
{
    public function triggerStart()
    {
        try {
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * @return array
     */
    public function getServerSettings(): array
    {
        $settings = $this->getSettings();
        $serverSettings = [];
        foreach ($settings as $key => $value) {
            if (str_starts_with($key, 'swoole_')) {
                $serverSettings[substr($key, 7)] = $value;
            }
        }
        return $serverSettings;
    }
}