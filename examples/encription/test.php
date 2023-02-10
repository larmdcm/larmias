<?php

use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\Contracts\CacheInterface;
use Larmias\Cache\Cache;
use Larmias\Contracts\Encryption\EncryptorInterface;

require '../bootstrap.php';

$container = require '../di/container.php';

$container->bind(ConfigInterface::class, Config::class);
$container->bind(CacheInterface::class, Cache::class);

$container->get(ConfigInterface::class)->load('./encryption.php');


/** @var EncryptorInterface $encryptor */
$encryptor = $container->get(\Larmias\Encryption\Encryptor::class);

$base64 = new \Larmias\Utils\Encryption\Base64();
$password = $encryptor->encrypt('1231232');
println($password);
$password = $base64->encode($password);
println($password);
println($encryptor->decrypt($base64->decode($password)));

$hash = \Larmias\Utils\Encryption\Hash::make('123456');
$password = $hash->get();
println($password);
var_dump($hash->check($password));
var_dump($hash->check(hash('md5','123456')));