<?php

require '../bootstrap.php';

use Larmias\Utils\Reflection\ReflectUtil;

$classes = ReflectUtil::readFileAllClass('./test_class.php');

dump($classes);