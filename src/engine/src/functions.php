<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Context\ApplicationContext;

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