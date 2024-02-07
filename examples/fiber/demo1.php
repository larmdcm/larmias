<?php

$fiber = new Fiber(function (): void {
    echo "Suspending...\n";
    $value = Fiber::suspend('inside');
    echo "Value used to resume fiber: ", $value, PHP_EOL;
});

$value = $fiber->start();
var_dump($value);
echo "Resuming...\n";
$fiber->resume();