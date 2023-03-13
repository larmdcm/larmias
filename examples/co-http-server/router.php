<?php

use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Contracts\ViewInterface;

Router::get('/', function (RequestInterface $request, ResponseInterface $response) {
    return $response->raw('Hello,World!');
});

Router::get('/param/{id:\d+}', function (ResponseInterface $response, $id = 0) {
    return $response->json([
        'id' => $id
    ]);
});

Router::get('/view', function (ViewInterface $view, RequestInterface $request, ResponseInterface $response) {
    $page = $request->query('page', 'home');
    $var = ['welcome' => 'Welcome,swoole engine！' . $page];
    if ($page !== 'home') {
        $view->with('data', $request->query());
    }
    return $response->html($view->render($page, $var));
});

Router::get('/write', function (ResponseInterface $response) {
    $response->write('哈哈哈');
    $response->write('123');
    return $response->withHeader('test', 123);
});