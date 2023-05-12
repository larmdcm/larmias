<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use function str_starts_with;

abstract class Server extends Worker
{
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