<?php

require '../bootstrap.php';

use Larmias\ShareMemory\Client;

$client = new Client();

var_dump($client->command('auth', ['123456']));
var_dump($client->command('select', ['map']));
var_dump($client->command('map:set', ['name', '测试']));
var_dump($client->command('map:get',['name']));
