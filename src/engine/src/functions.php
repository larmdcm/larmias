<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Context\ApplicationContext;
use function is_callable;
use function shell_exec;
use function strtolower;
use const DIRECTORY_SEPARATOR;

/**
 * 获取CPU核心数
 * @return int
 */
function get_cpu_num(): int
{
    if (!is_unix()) {
        return 1;
    }

    $count = 0;

    if (is_callable('shell_exec')) {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = (int)shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)shell_exec('nproc');
        }
    }

    return $count > 0 ? $count : 1;
}

/**
 * 判断是否为UNIX系统
 * @return bool
 */
function is_unix(): bool
{
    return DIRECTORY_SEPARATOR === '/';
}


/**
 * 运行引擎容器
 * @param callable $callback
 * @param array $settings
 * @return void
 */
function run(callable $callback, array $settings = []): void
{
    $run = new Run(ApplicationContext::getContainer());
    $run->set($settings);
    $run($callback);
}