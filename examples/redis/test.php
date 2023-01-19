<?php

require '../bootstrap.php';

/** @var \Larmias\Redis\Redis $redis */
$redis = require './redisHandler.php';

var_dump($redis->get('redis'));