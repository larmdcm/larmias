<?php

require '../bootstrap.php';

use Larmias\Http\Utils\Request\Client;

$client = new Client();

$response = $client->get('https://www.baidu.com');

dump($response->getBody()->getContents());