<?php

use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\Contracts\CacheInterface;
use Larmias\Cache\Cache;

require '../bootstrap.php';

$container = require '../di/container.php';

$container->bind(ConfigInterface::class,Config::class);
$container->bind(CacheInterface::class,Cache::class);

$container->get(ConfigInterface::class)->load('./cache.php');


/** @var CacheInterface $cache */
$cache = $container->get(CacheInterface::class);

var_dump($cache->get('test','no test'));
var_dump($cache->set('test',1,2));
var_dump($cache->get('test','no test'));
sleep(3);
var_dump($cache->get('test','sleep after no test.'));

var_dump($cache->increment('test_number'));
var_dump($cache->get('test_number'));
var_dump($cache->decrement('test_number'));
var_dump($cache->get('test_number'));
var_dump($cache->delete('test_number'));
var_dump($cache->remember('test_number',1,2));
var_dump($cache->delete('test_number'));
var_dump($cache->get('test_number','delete after no test_number.'));