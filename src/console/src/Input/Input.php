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
        $this->parse();
    }

    /**
     * @param Definition $definition
     * @return void
     */
    public function setDefinition(Definition $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getOption(string $name): ?string
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

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return void
     */
    protected function parse(): void
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
                    $this->arguments[]= $name;
                } else {
                    $this->options = $value;
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