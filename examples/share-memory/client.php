<?php

require '../bootstrap.php';

use Larmias\ShareMemory\Client;

$client = new Client();

var_dump($client->command('map', ['set', 'name', '测试']));