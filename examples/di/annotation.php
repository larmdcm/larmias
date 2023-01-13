<?php

require '../bootstrap.php';

use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require './container.php';


foreach (glob('./annotation/*.php') as $file) {
    require_once $file;
}

foreach (glob('./classes/*.php') as $file) {
    require_once $file;
}


$annotation = new Annotation($container,[
    'include_path' => ['./classes']
]);

AnnotationManager::init($annotation);

AnnotationManager::scan();

$a = $container->make(\Di\A::class);

$a->invoke();