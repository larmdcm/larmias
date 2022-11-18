<?php

require '../bootstrap.php';

use Larmias\Routing\Router;

$container = require '../di/container.php';

$router = new Router($container);

$router->rule('GET','/',function () {
    echo "hello,world!";
});

$router->group('/v1',function (Router $router)  {
    $router->rule('GET','/get/{id}',function () {
        echo 'v1 get call.';
    })->namespace('\\V1')->middleware(['getMiddle']);

    $router->group(['prefix' => '/api','namespace' => '\\Api'],function (Router $router) {
        $router->rule('POST','/post',function () {
            echo 'v1 api post call.';
        });
    })->middleware(['checkAuthMiddle']);
})->namespace('\\App\\Http\\Controllers')->middleware(['test1Middle','test2Middle']);

$router->dispatch('GET','/v1/get/1');