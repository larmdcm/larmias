<?php

declare(strict_types=1);

namespace Larmias\Throttle\Middleware;

use Larmias\Contracts\ThrottleInterface;
use Larmias\Throttle\Exceptions\RateLimitOfErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function max;
use function md5;

class ThrottleMiddleware implements MiddlewareInterface
{
    /**
     * @var bool
     */
    protected bool $showRateLimit = true;

    /**
     * @param ThrottleInterface $throttle
     */
    public function __construct(protected ThrottleInterface $throttle)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->allowRequest($request)) {
            $this->rateLimited($request);
        }
        $response = $handler->handle($request);

        return $this->addRateLimitHeaderToResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function addRateLimitHeaderToResponse(ResponseInterface $response): ResponseInterface
    {
        if ($this->showRateLimit && 200 <= $response->getStatusCode() && 300 > $response->getStatusCode()) {
            $allowInfo = $this->throttle->getAllowInfo();
            $response = $response->withHeader('X-Rate-Limit-Limit', $allowInfo['max_requests'])
                ->withHeader('X-Rate-Limit-Remaining', max($allowInfo['remaining'], 0))
                ->withHeader('X-Rate-Limit-Reset', $allowInfo['now_time'] + $allowInfo['expire']);
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function allowRequest(ServerRequestInterface $request): bool
    {
        $key = $this->getKey($request);
        $rateLimit = $this->getRateLimit();
        return $this->throttle->allow($key, $rateLimit);
    }

    /**
     * @return array
     */
    protected function getRateLimit(): array
    {
        return [60, 60];
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getKey(ServerRequestInterface $request): string
    {
        return md5($request->getUri()->getPath());
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    protected function rateLimited(ServerRequestInterface $request): void
    {
        $e = new RateLimitOfErrorException();
        $e->setWaitSeconds($this->throttle->getAllowInfo()['wait_seconds'] ?? 0);
        throw $e;
    }
}