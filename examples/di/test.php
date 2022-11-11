<?php

require '../bootstrap.php';
require './test_class.php';

use Larmias\Di\Container;

$container = Container::getInstance();

$container->bind('test1',A::class);
$container->bind('b',B::class);
/** @var A $test1 */
$test1 = $container->make('b');
$test1->echo();