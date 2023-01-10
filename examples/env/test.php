<?php

require '../bootstrap.php';

$env = new \Larmias\Env\DotEnv();

$env->load('./.env.example');

dump($env->get('APP_NAME'));
dump($env->get('EMPTY'));
dump($env->get('LOG_DEPRECATIONS_CHANNEL'));
dump($env->get('MAIL_FROM_NAME'));
dump($env->get('AWS_ACCESS_KEY_ID'));
dump($env->get('APP_DEBUG'));

dump($env->has('SET'));
$env->set('SET','true');
dump($env->get('SET'));
dump($env->get('DB_PASSWORD'));

