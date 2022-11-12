<?php

declare(strict_types=1);

namespace Larmias\Routing;

class Rule
{
    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $path;

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
     * Rule __construct
     *
     * @param string $method
     * @param string $path
     * @param mixed $handler
     * @param array $option
     */
    public function __construct(string $method, string $path, $handler, array $groupNumbers = [], array $option = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
        $this->groupNumbers = $groupNumbers;
        $this->option = \array_merge($option, self::getDefaultOption());
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
     * Get the value of path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @param string $path
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
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
     * @return array
     */
    public function getOption(): array
    {
        return $this->option;
    }

    /**
     * Set 规则选项.
     *
     * @param array $option 规则选项.
     * @return self
     */
    public function setOption(array $option): self
    {
        $this->option = array_merge($this->option, $option);
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
            'path' => $this->path,
            'handler' => $this->handler,
            'groupNumbers' => $this->groupNumbers,
            'option' => $this->option,
        ];
    }
}