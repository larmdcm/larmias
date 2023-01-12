<?php

require '../bootstrap.php';

use Larmias\Utils\Reflection\ReflectUtil;

$classes = ReflectUtil::getAllClassesInFile('./test_class.php');

dump($classes);