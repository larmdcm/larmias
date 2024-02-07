<?php

use Larmias\Contracts\ApplicationInterface;

require __DIR__ . '/../vendor/autoload.php';

/** @var ApplicationInterface $app */
$app = require __DIR__ . '/bootstrap.php';

return $app;