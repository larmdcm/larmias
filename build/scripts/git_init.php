<?php

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Finder\Finder;

$projectSrc = PROJECT_PATH . '/src';


$dirs = Finder::create()->files()->in([$projectSrc])->depth(0)->directories();

foreach (iterator_to_array($dirs) as $dirItem) {
    $path = $dirItem->getRealPath();
    $name = basename($path);
    $command = "cd {$path} && git init && git add . && git commit -m init && git branch -M main &&  git remote add origin git@github.com:larmdcm/{$name}.git";
    $result = shell_exec($command);
    var_dump($result);
    exit;
}

