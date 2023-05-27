<?php

declare(strict_types=1);

namespace Larmias\Routing;

use Larmias\Contracts\ContainerInterface;
use Larmias\Routing\Contracts\RouterInterface;
use Larmias\Routing\Exceptions\RouteException;
use Larmias\Routing\Exceptions\RouteNotFoundException;
use Larmias\Routing\Exceptions\RouteMethodNotAllowedException;
use Larmias\Utils\Arr;
use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use function count;
use function strtoupper;
use function is_callable;
use function is_string;
use function is_file;
use function is_array;
use function call_user_func;
use function end;
use function implode;

class Router implements RouterInterface
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
     * @var RouteName
     */
    protected RouteName $routeName;

    /**
     * @var boolean
     */
    protected bool $isCollectRoute = false;

    /**
     * @var boolean
     */
    protected bool $isCollectGroup = false;

    /**
     * @var FastRouteDispatcher
     */
    protected FastRouteDispatcher $dispatcher;

    /**
     * @var array
     */
    protected array $queue = [];

    /**
     * RouterAbstract __construct.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->group = new Group();
        $this->routeName = new RouteName();
    }

    /**
     * add route rule.
     *
     * @param string|array $method
     * @param string $route
     * @param mixed $handler
     * @return self
     */
    public function rule(string|array $method, string $route, mixed $handler): self
    {
        $methods = Arr::wrap($method);
        $count = count($this->rules);
        $queue = [];
        foreach ($methods as $method) {
            $routes = $route === '/' || $route === '' ? [$route, $route === '/' ? '' : '/'] : [$route];
            foreach ($routes as $routeItem) {
                $rule = new Rule(strtoupper($method), $routeItem, $handler, $this->group->getGroupNumbers());
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
        if (is_callable($option)) {
            $handler = $option;
        } else if (is_string($option)) {
            if (is_file($option)) {
                $handler = function () use ($option) {
                    require_once $option;
                };
            } else {
                $params['prefix'] = $option;
            }
        } else if (is_array($option)) {
            $params = $option;
        }
        $this->group->create($params, function ($groupNumbers) use ($handler) {
            is_callable($handler) && call_user_func($handler, $this);
            $this->queue = [['type' => self::TYPE_GROUP, 'index' => end($groupNumbers)]];
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
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        return $this->addOption(__FUNCTION__, $name);
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
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param string|int $name
     * @return Rule|null
     */
    public function getRule(string|int $name): ?Rule
    {
        $index = is_string($name) ? $this->routeName->get($name) : $name;
        return $this->rules[$index] ?? null;
    }

    /**
     * 路由调度.
     *
     * @param string $method
     * @param string $route
     * @return Dispatched
     */
    public function dispatch(string $method, string $route): Dispatched
    {
        $this->collectGroup();
        $this->collectRoute();

        $routeInfo = $this->dispatcher->dispatch($method, $route);
        if ($routeInfo[0] === FastRouteDispatcher::FOUND) {
            /** @var Rule $rule */
            $rule = $routeInfo[1];
            return new Dispatched(Dispatcher::create($this->container, $rule), $rule, $routeInfo[2]);
        }
        throw new ($routeInfo[0] === FastRouteDispatcher::NOT_FOUND ? RouteNotFoundException::class : RouteMethodNotAllowedException::class);
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
            $rule->setRoute(
                implode('', [...Arr::wrap($option['prefix'] ?? []), $ruleOption['prefix'] ?? '', $rule->getRoute()])
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
                $routeCollector->addRoute($rule->getMethod(), $rule->getRoute(), $rule);
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
            throw new RouteException('Routing rule or group has not been added.');
        }
        foreach ($this->queue as $item) {
            if ($item['type'] === self::TYPE_RULE) {
                $rule = $this->rules[$item['index']];
                if ($key === 'name') {
                    $rule->setName($value);
                    $this->routeName->set($value, $item['index']);
                } else {
                    $rule->setOption([$key => $value]);
                }
            } else {
                $this->group->setOption($item['index'], [$key => $value]);
            }
        }
        return $this;
    }
}