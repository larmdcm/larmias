<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

class Definition
{
    /** @var Argument[] */
    protected array $arguments = [];

    /** @var Option[] */
    protected array $options = [];

    /** @var string[] */
    protected array $shortcuts = [];

    /**
     * @param string $name
     * @param int $mode
     * @param string $description
     * @param mixed|null $default
     * @return self
     */
    public function addArgument(string $name, int $mode = Argument::REQUIRED, string $description = '', mixed $default = null): self
    {
        $this->arguments[] = new Argument($name, $mode, $description, $default);
        return $this;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string|int $name
     * @return Argument|null
     */
    public function getArgument(string|int $name): ?Argument
    {
        if (!$this->hasArgument($name)) {
            return null;
        }

        $arguments = is_int($name) ? array_values($this->arguments) : $this->arguments;

        return $arguments[$name];
    }

    /**
     * @param string|int $name
     * @return bool
     */
    public function hasArgument(string|int $name): bool
    {
        $arguments = is_int($name) ? array_values($this->arguments) : $this->arguments;
        return isset($arguments[$name]);
    }

    /**
     * @param string $name
     * @param string|null $shortcut
     * @param int $mode
     * @param string $description
     * @param mixed|null $default
     * @return self
     */
    public function addOption(string $name, ?string $shortcut = null, int $mode = Option::VALUE_NONE, string $description = '', mixed $default = null): self
    {
        if ($this->hasOption($name)) {
            throw new \LogicException(sprintf('An option named "%s" already exists.', $name));
        }
        $option = new Option($name, $shortcut, $mode, $description, $default);
        $this->options[] = $option;
        if ($shortcut) {
            foreach (explode('|', $shortcut) as $shortcut) {
                $this->shortcuts[$name] = $shortcut;
            }
        }
        return $this;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @return Option|null
     */
    public function getOption(string $name): ?Option
    {
        $name = $this->getLongOptionName($name);
        return $this->hasOption($name) ? $this->options[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$this->getLongOptionName($name)]);
    }

    /**
     * 获取参数默认值
     * @return array
     */
    public function getArgumentDefaults(): array
    {
        $values = [];
        foreach ($this->arguments as $argument) {
            $values[$argument->getName()] = $argument->getDefault();
        }

        return $values;
    }

    /**
     * 获取所有选项的默认值
     *
     * @return array
     */
    public function getOptionDefaults(): array
    {
        $values = [];
        foreach ($this->options as $option) {
            $values[$option->getName()] = $option->getDefault();
        }
        return $values;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getLongOptionName(string $name): string
    {
        return $this->shortcuts[$name] ?? $name;
    }
}