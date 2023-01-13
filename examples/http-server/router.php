<?php

use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;

Router::get('/', function (ResponseInterface $response) {
    return $response->raw('Hello,World!');
});

Router::get('/test/{id:\d+}', function (ResponseInterface $response, $id = 0) {
    return $response->json([
        'id' => $id
    ]);
});

Router::get('/middleware', function (RequestInterface $request, ResponseInterface $response) {
    return $response->raw($request->getPathInfo());
})->middleware([
    CheckAuth::class,
]);

Router::get('/auto', function () {
    return ['name' => 123];
});

Router::get('/upload', function (RequestInterface $request, ResponseInterface $response) {
    return $response->html(file_get_contents('./views/upload.html'));
});

Router::post('/upload', function (RequestInterface $request, ResponseInterface $response) {
    $file = $request->file('file');
    $file->moveTo('./upload/a.jpg');
    return $response->html(file_get_contents('./views/upload.html'));
});