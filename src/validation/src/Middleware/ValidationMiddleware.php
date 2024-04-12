<?php

declare(strict_types=1);

namespace Larmias\Validation\Middleware;

use FastRoute\Dispatcher;
use Larmias\Di\Annotation\Inject;
use Larmias\Di\AnnotationCollector;
use Larmias\HttpServer\Exceptions\HttpException;
use Larmias\HttpServer\Message\Request;
use Larmias\Routing\Dispatched;
use Larmias\Support\Helper;
use Larmias\Validation\Annotation\Validate;
use Larmias\Validation\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;
use function Larmias\Support\make;

class ValidationMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected Request $request;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (!$dispatched instanceof Dispatched) {
            throw new HttpException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        if ($this->shouldHandle($dispatched)) {
            [$requestHandler, $method] = Helper::prepareHandler($dispatched->rule->getHandler());
            /** @var Validate[] $validateList */
            $validateList = AnnotationCollector::get(sprintf('%s.method.%s.%s', $requestHandler, $method, Validate::class));
            foreach ($validateList as $validate) {
                $instance = make($validate->validate, [], true);
                if ($instance instanceof Validator) {
                    if ($validate->scene) {
                        $instance->scene($validate->scene);
                    }
                    $instance->data($this->request->all())
                        ->batch($validate->batch)
                        ->failException(true)
                        ->fails();
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param Dispatched $dispatched
     * @return bool
     */
    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && !$dispatched->rule->getHandler() instanceof Closure;
    }
}