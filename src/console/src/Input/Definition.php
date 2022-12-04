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
     * @return $this
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
     * @param string $name
     * @return Argument|null
     */
    public function getArgument(string $name): ?Argument
    {
        return $this->hasArgument($name) ? $this->arguments[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
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
            foreach (explode('|',$shortcut) as $shortcut) {
                $this->shortcuts[$name] = $shortcut;
            }
        }
        return $this;
    }

    /**
     * @return Option[]
     */
    public function geOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @return Option|null
     */
    public function getOption(string $name): ?Option
    {
        return $this->hasOption($name) ? $this->options[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }
}