<?php

declare(strict_types=1);

namespace Larmias\Routing;

use Larmias\Contracts\ContainerInterface;
use Larmias\Utils\Arr;
use RuntimeException;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    /**
     * @var int
     */
    public const TYPE_RULE = 1;

    /**
     * @var int
     */
    public const TYPE_GROUP = 2;

    /**
     * @var Rule[]
     */
    protected array $rules = [];

    /**
     * @var Group
     */
    protected Group $group;

    /**
     * @var array
     */
    protected array $groupNumbers = [];

    /**
     * @var boolean
     */
    protected bool $isCollectRoute = false;

    /**
     * @var boolean
     */
    protected bool $isCollectGroup = false;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;


    /**
     * @var array
     */
    protected array $queue = [];

    /**
     * RouterAbstract __construct.
     *
     * @param \Larmias\Contracts\ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->group = new Group();
    }

    /**
     * add route rule.
     *
     * @param string|array $method
     * @param string $path
     * @param mixed $handler
     * @return self
     */
    public function rule(string|array $method, string $path, mixed $handler): self
    {
        $methods = Arr::wrap($method);
        $count = \count($this->rules);
        $queue = [];
        foreach ($methods as $method) {
            $paths = $path === '/' || $path === '' ? [$path, $path === '/' ? '' : '/'] : [$path];
            foreach ($paths as $item) {
                $rule = new Rule(\strtoupper($method), $item, $handler, $this->group->getGroupNumbers());
                $this->rules[] = $rule;
                $queue[] = ['type' => self::TYPE_RULE, 'index' => $count++];
            }
        }
        $this->queue = $queue;
        return $this;
    }

    /**
     * add route group.
     *
     * @param callable|array|string $option
     * @param callable|null $handler
     * @return self
     */
    public function group(callable|array|string $option, ?callable $handler = null): self
    {
        $params = [];
        if (\is_callable($option)) {
            $handler = $option;
        } else if (\is_string($option)) {
            if (\is_file($option)) {
                $handler = function () use ($option) {
                    require_once $option;
                };
            } else {
                $params['prefix'] = $option;
            }
        } else if (\is_array($option)) {
            $params = $option;
        }
        $this->group->create($params, function ($groupNumbers) use ($handler) {
            \is_callable($handler) && \call_user_func($handler, $this);
            $this->queue = [['type' => self::TYPE_GROUP, 'index' => \end($groupNumbers)]];
        });
        return $this;
    }

    /**
     * add route prefix
     *
     * @param string $prefix
     * @return self
     */
    public function prefix(string $prefix): self
    {
        return $this->addOption(__FUNCTION__, $prefix);
    }

    /**
     * add route middleware
     *
     * @param string|array $middleware
     * @return self
     */
    public function middleware(string|array $middleware): self
    {
        return $this->addOption(__FUNCTION__, Arr::wrap($middleware));
    }

    /**
     * add route namespace
     *
     * @param string $namespace
     * @return self
     */
    public function namespace(string $namespace): self
    {
        return $this->addOption(__FUNCTION__, $namespace);
    }

    /**
     * 路由调度.
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public function dispatch(string $method, string $path, array $params = []): mixed
    {
        $this->collectGroup();
        $this->collectRoute();
        $routeInfo = $this->dispatcher->dispatch($method(), $path);
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                /** @var \Larmias\Routing\Rule $rule */
                $rule = $routeInfo[1];
                break;
            case Dispatcher::NOT_FOUND:
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                break;
            default:
                break;
        }
        return null;
    }

    /**
     * @return void
     */
    protected function collectGroup(): void
    {
        if ($this->isCollectGroup) {
            return;
        }
        foreach ($this->rules as $rule) {
            $option = $this->group->getOption($rule->getGroupNumbers());
            $ruleOption = $rule->getOption();
            $rule->setPath(
                implode('', [...Arr::wrap($option['prefix'] ?? []), $ruleOption['prefix'] ?? '', $rule->getPath()])
            );
            $ruleOption['middleware'] = [...Arr::wrap($option['middleware'] ?? []), ...Arr::wrap($ruleOption['middleware'] ?? [])];
            $ruleOption['namespace'] = implode('', [...Arr::wrap($option['namespace'] ?? []), $ruleOption['namespace'] ?? '']);
            $rule->setOption($ruleOption);
        }
        $this->isCollectGroup = true;
    }

    /**
     * 路由收集.
     *
     * @return void
     */
    protected function collectRoute(): void
    {
        if ($this->isCollectRoute) {
            return;
        }
        $this->dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
            foreach ($this->rules as $rule) {
                $routeCollector->addRoute($rule->getMethod(), $rule->getPath(), $rule);
            }
        });
        $this->isCollectRoute = true;
    }


    /**
     * add route option.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    protected function addOption(string $key, mixed $value): self
    {
        if (empty($this->queue)) {
            throw new RuntimeException('Routing rule or group has not been added.');
        }
        foreach ($this->queue as $item) {
            if ($item['type'] === self::TYPE_RULE) {
                $this->rules[$item['index']]->setOption([$key => $value]);
            } else {
                $this->group->setOption($item['index'], [$key => $value]);
            }
        }
        return $this;
    }
}