<?php

use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

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

Router::get('/upload', function (RequestInterface $request, ResponseInterface $response, \Larmias\Contracts\ViewInterface $view) {
    return $response->html($view->render('upload'));
});

Router::post('/upload', function (RequestInterface $request, ResponseInterface $response, \Larmias\Contracts\ViewInterface $view) {
    $file = $request->file('file');
    $file->moveTo('./upload/a.jpg');
    return $response->html($view->render('upload'));
});

Router::get('/cookie', function (RequestInterface $request, ResponseInterface $response) {
    $value = $request->query('value');
    if ($value) {
        return $response->withCookie(new \Larmias\Http\Message\Cookie('ckValue', $value));
    }
    return $request->cookie($request->query('key', 'null'), 'null');
});

Router::get('/session', function (\Larmias\Contracts\SessionInterface $session, ResponseInterface $response) {
    $session->set('name', 'session');
    return $session->get('name');
})->middleware(\Larmias\Session\Middleware\SessionMiddleware::class);

Router::get('/exception', function () {
    throw new RuntimeException('发生了异常');
});

Router::get('/url/{id}[/{name:\w+}]', function (RequestInterface $request) {
    return [
        'route' => $request->route(),
//        \Larmias\HttpServer\url_string('http://localhost:9863/index/url/{id}/{name}.html?a=123#page=1&page_size=25', ['id' => 1, 'b' => 666, 'name' => 'url']),
//        \Larmias\HttpServer\url_string('/index/url/{id}/{name}?a=123#page=1&page_size=25', ['id' => 1, 'b' => 666, 'name' => 'url']),
        \Larmias\HttpServer\url_string('index.url', ['id' => 1, 'name' => '哈哈哈', 'age' => 25]),
//        \Larmias\HttpServer\url_string('/url/{id}[/{name}]/{id2}[/{name2}]'),
    ];
})->name('index.url');

Router::rule(['GET', 'POST'], '/csrf', function (RequestInterface $request, \Larmias\Contracts\ViewInterface $view, ResponseInterface $response) {
    return $response->html($view->render('csrf'));
})->middleware([
    \Larmias\Session\Middleware\SessionMiddleware::class,
    \Larmias\Http\CSRF\Middleware\CsrfMiddleware::class,
]);

Router::get('/captcha', function (PsrResponseInterface $response) {
    $captcha = new \Larmias\Captcha\Captcha();
    $result = $captcha->create();
    return $response->withHeader('Content-Type', $result->getMimeType())
        ->withBody(\Larmias\Http\Message\Stream::create($result->getContent()));
});

Router::get('/snowflake', function (\Larmias\Snowflake\Contracts\IdGeneratorInterface $idGenerator, \Larmias\Engine\Contracts\WorkerInterface $worker) {
    $id = $idGenerator->id();
    $file = __DIR__ . '/runtime/id.txt';
    \file_put_contents($file, $id . PHP_EOL, FILE_APPEND);
    return $id;
});

Router::get('/task', function (\Larmias\Task\TaskExecutor $executor) {
    return $executor->execute(function () {
        println('task call.');
    });
});

Router::get('/auth', function () {
    return '登录成功-' . \Larmias\Auth\Facade\Auth::id();
})->middleware([
    \Larmias\Session\Middleware\SessionMiddleware::class,
    \Larmias\Auth\Middleware\AuthenticateMiddleware::class
]);

Router::get('/auth/login', function () {
    \Larmias\Auth\Facade\Auth::attempt(['username' => 'test']);
    return '登录成功-' . \Larmias\Auth\Facade\Auth::id();
})->middleware([
    \Larmias\Session\Middleware\SessionMiddleware::class,
]);

Router::get('/file', function (ResponseInterface $response) {
    return $response->file('./upload/a.jpg');
});

Router::get('/download', function (ResponseInterface $response) {
    return $response->download('./upload/a.jpg');
});

Router::get('/write', function (ResponseInterface $response) {
    $response->write('哈哈哈');
    $response->write('123');
    return '';
});

Router::get('/rateLimit', function (ResponseInterface $response) {
    return 'ok';
})->middleware(\Larmias\Throttle\Middleware\ThrottleMiddleware::class);