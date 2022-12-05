<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

use Larmias\Console\Contracts\InputInterface;

class Input implements InputInterface
{
    /** @var string */
    protected string $scriptFile;

    /** @var string */
    protected string $command = '';

    /** @var array */
    protected array $tokens;

    /** @var string[] */
    protected array $data = [
        'arguments' => [],
        'options' => [],
    ];

    /** @var array */
    protected array $arguments = [];

    /** @var array */
    protected array $options = [];

    /** @var Definition */
    protected Definition $definition;

    /**
     * @param array|null $argv
     */
    public function __construct(?array $argv = null)
    {
        if (\is_null($argv)) {
            $argv = $_SERVER['argv'];
        }
        $this->scriptFile = (string)array_shift($argv);
        if (isset($argv[0]) && !str_starts_with($argv[0], '-') && !str_starts_with($argv[0], '--')) {
            $this->command = (string)array_shift($argv);
        }
        $this->definition = new Definition();
        $this->tokens = array_values((array)$argv);
        $this->parseToken();
    }

    /**
     * @param Definition $definition
     * @return InputInterface
     */
    public function bind(Definition $definition): InputInterface
    {
        $this->definition = $definition;
        $this->arguments = [];
        $this->options = [];
        $this->parse();
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getArgument(string $name): mixed
    {
        if (!$this->definition->hasArgument($name)) {
            throw new \InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        return $this->arguments[$name] ?? $this->definition->getArgument($name)->getDefault();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        return $this->definition->hasArgument($name) && isset($this->arguments[$name]);
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return array_merge($this->definition->getArgumentDefaults(), $this->arguments);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption(string $name): mixed
    {
        if (!$this->definition->hasOption($name)) {
            throw new \InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }
        return $this->options[$name] ?? $this->definition->getOption($name)->getDefault();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return $this->definition->hasOption($name) && isset($this->options[$name]);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_merge($this->definition->getOptionDefaults(), $this->options);
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * 获取输入的参数
     *
     * @param string|int $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getInputParam(string|int $key, mixed $default = null): mixed
    {
        $name = is_int($key) ? 'arguments' : 'options';
        return $this->data[$name][$key] ?? $default;
    }

    /**
     * 输入的参数是否存在
     *
     * @param string|int $key
     * @return bool
     */
    public function hasInputParam(string|int $key): bool
    {
        $name = is_int($key) ? 'arguments' : 'options';
        return isset($this->data[$name][$key]);
    }

    /**
     * @return void
     */
    protected function parse(): void
    {
        foreach ($this->definition->getOptions() as $option) {
            $name = $option->getName();
            $value = false;
            if ($this->hasInputParam($name)) {
                $value = $this->getInputParam($name);
            } else if ($this->hasInputParam($option->getShortcut())) {
                $value = $this->getInputParam($option->getShortcut());
            }
            if ($option->isValueRequired() && $value === false) {
                throw new \InvalidArgumentException(sprintf('The "%s" options Required.', $name));
            }
            if ($option->isAcceptValue()) {
                $this->options[$name] = $value;
            }
        }

        foreach ($this->definition->getArguments() as $argument) {
            $name = $argument->getName();
            $value = false;
            $key = count($this->arguments);
            if ($this->hasInputParam($key)) {
                $value = $this->getInputParam($key);
            }
            if ($argument->isRequired() && $value === false) {
                throw new \InvalidArgumentException(sprintf('The "%s" arguments Required.', $name));
            }
            $this->arguments[$name] = $value;
        }
    }

    /**
     * @return void
     */
    protected function parseToken(): void
    {
        $key = 0;
        while (true) {
            if (!isset($this->tokens[$key])) {
                break;
            }
            $item = $this->tokens[$key];
            $isArgument = false;
            $name = null;
            $value = null;
            if (str_contains($item, '=')) {
                [$name, $value] = explode('=', $item, 2);
            } else if ($this->isOptionInstruct($item)) {
                $name = $item;
                $nextKey = $key + 1;
                if (isset($this->tokens[$nextKey]) && $this->tokens[$nextKey] && !$this->isOptionInstruct($this->tokens[$nextKey])) {
                    $value = $this->tokens[$nextKey];
                    $key++;
                }
            } else {
                $name = $item;
                $isArgument = true;
            }

            if ($name) {
                $name = ltrim($name, '-');
                if ($isArgument) {
                    $this->data['arguments'][] = $name;
                } else {
                    $this->data['options'][$name] = $value;
                }
            }
            $key++;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isOptionInstruct(string $name): bool
    {
        return str_starts_with($name, '-') || str_starts_with($name, '--');
    }

    /**
     * @return string
     */
    public function getScriptFile(): string
    {
        return $this->scriptFile;
    }
}