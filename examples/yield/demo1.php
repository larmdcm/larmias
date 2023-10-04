<?php

function yield_func(): Generator
{
    $get = yield 1;
    echo $get . PHP_EOL;
    yield 2;
    return 'a';
}

$gen = yield_func();
var_dump($gen->current());
$gen->send("test");
var_dump($gen->current());
$gen->next();
var_dump($gen->getReturn());