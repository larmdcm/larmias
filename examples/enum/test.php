<?php

require '../bootstrap.php';

foreach (glob('./classes/*.php') as $file) {
    require_once $file;
}

println(\Enum\UserEnum::STATUS_ENABLE);
println(\Enum\UserEnum::STATUS_ENABLE());
println(\Enum\UserEnum::STATUS_ENABLE('label'));
println(\Enum\UserEnum::STATUS_ENABLE('value'));
println(\Enum\UserEnum::STATUS_ENABLE()->getValue() === 1);
println(\Enum\UserEnum::getText(\Enum\UserEnum::STATUS_ENABLE));
