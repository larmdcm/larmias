<?php

declare(strict_types=1);

namespace Larmias\Engine;

function get_cpu_num(): int
{
    if (!is_unix()) {
        return 1;
    }
    $count = 0;
    if (\is_callable('shell_exec')) {
        if (\strtolower(PHP_OS) === 'darwin') {
            $count = (int)shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)shell_exec('nproc');
        }
    }
    return $count > 0 ? $count : 1;
}

function is_unix(): bool
{
    return \DIRECTORY_SEPARATOR === '/';
}