<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Constants;
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
        $settingMap = [
            'enable_reuse_port' => Constants::OPTION_REUSE_PORT,
            'package_max_length' => Constants::OPTION_PACKAGE_MAX_LENGTH,
            'backlog' => Constants::OPTION_BACKLOG,
        ];

        foreach ($settings as $key => $value) {
            if (str_starts_with($key, 'swoole_')) {
                $serverSettings[substr($key, 7)] = $value;
            }
        }

        foreach ($settingMap as $key => $opt) {
            if (isset($settings[$opt])) {
                $serverSettings[$key] = $settings[$opt];
            }
        }

        return $serverSettings;
    }
}