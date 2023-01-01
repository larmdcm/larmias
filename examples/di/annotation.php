<?php

require '../bootstrap.php';

use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require './container.php';

foreach (glob('./classes/*.php') as $class) {
    require $class;
}

foreach (glob('./annotation/*.php') as $class) {
    require $class;
}

$annotation = new Annotation($container,[
    'include_path' => ['./classes']
]);

AnnotationManager::init($annotation);

AnnotationManager::scan();

$a = $container->make(\Di\A::class);

$a->index();