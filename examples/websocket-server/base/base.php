#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

require '../bootstrap.php';

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

$container = require '../di/container.php';

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build(require __DIR__ . '/base_config.php'));

$kernel->run();