<?php

declare(strict_types=1);

namespace Larmias\Routing;

class Rule
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $route;

    /**
     * @var mixed
     */
    protected mixed $handler;

    /**
     * @var array
     */
    protected array $groupNumbers = [];

    /**
     * 规则选项.
     *
     * @var array
     */
    protected array $option = [];

    /**
     * @param string $method
     * @param string $route
     * @param mixed $handler
     * @param array $groupNumbers
     * @param array $option
     */
    public function __construct(string $method, string $route, mixed $handler, array $groupNumbers = [], array $option = [])
    {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
        $this->groupNumbers = $groupNumbers;
        $this->option = \array_merge($option, self::getDefaultOption());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (!isset($this->name)) {
            return $this->getMethod() . '::' . $this->getRoute();
        }
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the value of method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the value of route
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Set the value of route
     *
     * @param string $route
     * @return self
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get the value of handler
     *
     * @return mixed
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * Set the value of handler
     *
     * @param mixed $handler
     * @return self
     */
    public function setHandler(mixed $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * Get the value of groupNumbers
     *
     * @return array
     */
    public function getGroupNumbers(): array
    {
        return $this->groupNumbers;
    }

    /**
     * Set the value of groupNumbers
     *
     * @param array $groupNumbers
     * @return self
     */
    public function setGroupNumbers(array $groupNumbers): self
    {
        $this->groupNumbers = $groupNumbers;
        return $this;
    }

    /**
     * Get 规则选项.
     *
     * @param string|null $name
     * @return mixed
     */
    public function getOption(?string $name = null): mixed
    {
        return $name ? ($this->option[$name] ?? null) : $this->option;
    }

    /**
     * Set 规则选项.
     *
     * @param array $option 规则选项.
     * @return self
     */
    public function setOption(array $option): self
    {
        $this->option = \array_merge($this->option, $option);
        return $this;
    }

    /**
     * @return array
     */
    public static function getDefaultOption(): array
    {
        return [
            'prefix' => '',
            'middleware' => [],
            'namespace' => '',
        ];

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'route' => $this->route,
            'handler' => $this->handler,
            'groupNumbers' => $this->groupNumbers,
            'option' => $this->option,
        ];
    }
}