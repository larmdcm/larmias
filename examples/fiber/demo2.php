<?php

$fiber = new Fiber(function (): void {
    echo "Start...\n";
    sleep(1);
    echo "End...\n";
});

$fiber->start();
echo "Resuming...\n";
