<?php

declare(strict_types=1);

namespace Larmias\Middleware;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\MiddlewareInterface;
use Larmias\Contracts\PipelineInterface;
use Closure;

class Middleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected array $queue = [];

    /**
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * Middleware __construct
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        $this->initialize();
    }

    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        if ($this->type !== null) {
            $this->import((array)$this->config->get('middleware.' . $this->type, []));
        }
    }

    /**
     * 导入中间件
     *
     * @param array $middlewares
     * @return self
     */
    public function import(array $middlewares): self
    {
        foreach ($middlewares as $item) {
            $this->push($item);
        }
        return $this;
    }

    /**
     * 添加中间件到尾部
     *
     * @param string|Closure $middleware
     * @return self
     */
    public function push(string|Closure $middleware): self
    {
        $middleware = $this->build($middleware);
        if (!empty($middleware)) {
            $this->queue[] = $middleware;
        }
        return $this;
    }

    /**
     * 添加中间件到首部
     *
     * @param string|Closure $middleware
     * @return self
     */
    public function unshift(string|Closure $middleware): self
    {
        $middleware = $this->build($middleware);
        if (!empty($middleware)) {
            \array_unshift($this->queue, $middleware);
        }
        return $this;
    }

    /**
     * @param string|Closure $middleware
     * @return array
     */
    protected function build(string|Closure $middleware): array
    {
        $args = [];
        if ($middleware instanceof Closure) {
            return [$middleware, $args];
        }
        $middleGroup = $this->config->get('middleware.group.' . $middleware, []);
        if (!empty($middleGroup)) {
            $this->import((array)$middleGroup);
            return [];
        }
        if (str_contains($middleware, ':')) {
            $split = \explode(':', $middleware, 2);
            $middleware = $split[0];
            $args = \explode(',', $split[1]);
        }
        $aliasMiddleware = $this->config->get('middleware.alias.' . $middleware);
        if ($aliasMiddleware) {
            $middleware = $aliasMiddleware;
        }
        return [[$middleware, 'process'], $args];
    }

    /**
     * 管道调度.
     *
     * @return PipelineInterface
     */
    public function pipeline(): PipelineInterface
    {
        $pipeline = $this->container->make(PipelineInterface::class,[],true);
        return $pipeline->through(\array_map(function ($middleware) {
            return function ($request, $next) use ($middleware) {
                [$call, $params] = $middleware;
                if (\is_array($call) && \is_string($call[0])) {
                    $call = [$this->container->make($call[0]), $call[1]];
                }
                return \call_user_func($call, $request, $next, ...$params);
            };
        }, $this->queue));
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->queue;
    }
}