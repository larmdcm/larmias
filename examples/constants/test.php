<?php

require '../bootstrap.php';

foreach (glob('./classes/*.php') as $file) {
    require_once $file;
}

println(\Constants\Constants::STATUS_ENABLE);
println(\Constants\Constants::STATUS_DISABLE);
println(\Constants\Constants::getText(\Constants\Constants::STATUS_ENABLE));
println(\Constants\Constants::getLabel(\Constants\Constants::STATUS_ENABLE));
var_dump(\Constants\Constants::all());
