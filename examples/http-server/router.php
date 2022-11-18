<?php

use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;

Router::get('/',function (ResponseInterface $response) {
    return $response->raw('Hello,World!');
});

Router::get('/test/{id:\d+}',function (ResponseInterface $response,$id = 0) {
    return $response->json([
        'id' => $id
    ]);
});

Router::get('/middleware',function (RequestInterface $request,ResponseInterface $response) {
    return $response->raw($request->getPathInfo());
})->middleware([
    CheckAuth::class,
]);