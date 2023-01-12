<?php

//$a = [1, 2, 3];
//$b = [1, 2, 3, 6, 9, 66];
//
//var_dump($a + $b);
//var_dump(array_merge($a, $b));

$a1 = [
    'a' => 1,
    'b' => 2,
];

$a2 = [
    'a' => 1,
    'b' => 3,
];

var_dump($a1 + $a2);
var_dump(array_merge($a1, $a2));