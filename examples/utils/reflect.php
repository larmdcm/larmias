<?php

require '../bootstrap.php';

use Larmias\Support\Reflection\ReflectUtil;

$classes = ReflectUtil::getAllClassesInFile('./test_class.php');

var_dump($classes);