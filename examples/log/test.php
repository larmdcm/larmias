<?php

use Larmias\Contracts\LoggerInterface;

require '../bootstrap.php';

/** @var LoggerInterface $logger */
$logger = require './log.php';

$logger->debug('test');
$logger->error('test');
$logger->info('test');