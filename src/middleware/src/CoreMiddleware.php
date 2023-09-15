<?php

declare(strict_types=1);

namespace Larmias\Middleware;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\CoreMiddlewareInterface;
use Larmias\Contracts\PipelineInterface;
use Closure;
use function array_merge;
use function array_unshift;
use function explode;
use function array_map;
use function is_array;
use function is_string;
use function call_user_func;

class CoreMiddleware implements CoreMiddlewareInterface
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
     * @var array
     */
    protected array $globalMiddleware = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        if ($this->type !== null) {
            $this->import($this->globalMiddleware = (array)$this->config->get('middleware.' . $this->type, []));
        }
    }

    /**
     * 导入中间件
     * @param array $middleware
     * @return CoreMiddlewareInterface
     */
    public function import(array $middleware): CoreMiddlewareInterface
    {
        foreach ($middleware as $item) {
            $this->push($item);
        }
        return $this;
    }

    /**
     * 设置中间件
     * @param array $middleware
     * @return CoreMiddlewareInterface
     */
    public function set(array $middleware): CoreMiddlewareInterface
    {
        $list = array_merge($this->globalMiddleware, $middleware);
        $queue = [];
        foreach ($list as $item) {
            $middleware = $this->build($item);
            if (!empty($middleware)) {
                $queue[] = $middleware;
            }
        }
        $this->queue = $queue;
        return $this;
    }

    /**
     * 添加中间件到尾部
     * @param string|Closure $middleware
     * @return CoreMiddlewareInterface
     */
    public function push(string|Closure $middleware): CoreMiddlewareInterface
    {
        $middleware = $this->build($middleware);
        if (!empty($middleware)) {
            $this->queue[] = $middleware;
        }
        return $this;
    }

    /**
     * 添加中间件到首部
     * @param string|Closure $middleware
     * @return CoreMiddlewareInterface
     */
    public function unshift(string|Closure $middleware): CoreMiddlewareInterface
    {
        $middleware = $this->build($middleware);
        if (!empty($middleware)) {
            array_unshift($this->queue, $middleware);
        }
        return $this;
    }

    /**
     * 构建中间件
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
            $split = explode(':', $middleware, 2);
            $middleware = $split[0];
            $args = explode(',', $split[1]);
        }
        $aliasMiddleware = $this->config->get('middleware.alias.' . $middleware);
        if ($aliasMiddleware) {
            $middleware = $aliasMiddleware;
        }
        return [[$middleware, $this->getProcessName()], $args];
    }

    /**
     * 调度中间件
     * @param mixed $passable
     * @param Closure $handler
     * @return mixed
     */
    public function dispatch(mixed $passable, Closure $handler): mixed
    {
        return $this->pipeline()->send($passable)->then($handler);
    }

    /**
     * 管道调度.
     * @return PipelineInterface
     */
    protected function pipeline(): PipelineInterface
    {
        return $this->makePipeline()->through(array_map(function ($middleware) {
            return function ($request, $next) use ($middleware) {
                [$call, $params] = $middleware;
                if (is_array($call) && is_string($call[0])) {
                    $call = [$this->container->make($call[0]), $call[1]];
                }
                return call_user_func($call, $request, $this->warpHandler($next), ...$params);
            };
        }, $this->queue));
    }

    /**
     * @return PipelineInterface
     */
    protected function makePipeline(): PipelineInterface
    {
        /** @var PipelineInterface $pipeline */
        $pipeline = $this->container->make(PipelineInterface::class, [], true);
        return $pipeline;
    }

    /**
     * @param Closure $handler
     * @return mixed
     */
    protected function warpHandler(Closure $handler): mixed
    {
        return $handler;
    }

    /**
     * @return string
     */
    protected function getProcessName(): string
    {
        return 'process';
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->queue;
    }
}