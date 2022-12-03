<?php

require '../bootstrap.php';

$container = require '../di/container.php';

$console = new \Larmias\Console\Console($container);

$console->run();